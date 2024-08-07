// VAPID appPublic key
const strAppPublicKey =
  "BHa8YXOafmKseRbEAIcd3b_ZCe8GKYkb5PkMagrtq_ZR_0kkySggKXWAOUcpld6RKP1NHHncsP4NOsXgTBRqGWo";
// URL to save subscription on server via Fetch API
var baseUrl = window.location.origin;
const strSubscriberURL = baseUrl + "/save-subscription";

function encodeToUint8Array(strBase64) {
  var strPadding = "=".repeat((4 - (strBase64.length % 4)) % 4);
  strBase64 = (strBase64 + strPadding).replace(/\-/g, "+").replace(/_/g, "/");
  var rawData = atob(strBase64);
  var aOutput = new Uint8Array(rawData.length);
  for (i = 0; i < rawData.length; ++i) {
    aOutput[i] = rawData.charCodeAt(i);
  }
  return aOutput;
}

/**
 * check if PN already subscribed
 */
async function sw_isSubscribed() {
  var swReg;
  if (pn_isAvailable()) {
    swReg = await navigator.serviceWorker.getRegistration();
  }
  return swReg !== undefined;
}

/**
 * checks whether all requirements for PN are met
 * 1. have to run in secure context
 *    - window.isSecureContext = true
 * 2. browser should implement at least
 *    - navigatpr.serviceWorker
 *    - window.PushManager
 *    - window.Notification
 */
function pn_isAvailable() {
  var bAvailable = false;
  if (window.isSecureContext) {
    // running in secure context - check for available Push-API
    bAvailable =
      "serviceWorker" in navigator &&
      "PushManager" in window &&
      "Notification" in window;
  } else {
    console.log("site have to run in secure context!");
  }
  return bAvailable;
}

/**
 * register the service worker.
 * there is no check for multiple registration necessary - browser/Push-API
 * takes care if same service-worker ist already registered
 */
async function sw_register() {
  if ("serviceWorker" in navigator) {
    try {
      const registration = await navigator.serviceWorker.register(
        window.location.origin + "/service-worker.js"
      );
      if (registration.installing) {
        console.log("Service worker installing");
      } else if (registration.waiting) {
        console.log("Service worker installed. Scope is " + registration.scope);
      } else if (registration.active) {
        console.log("Service worker active");
      }
    } catch (error) {
      console.error(`Registration failed with ${error}`);
    }
  }
}

/**
 * unregister the service worker.
 */
async function sw_unregister() {
  if (await sw_isSubscribed()) {
    navigator.serviceWorker.getRegistration().then(function (reg) {
      reg.unregister().then(function (response) {
        if (response) {
          console.log("unregister service worker succeeded.");
        } else {
          console.log("unregister service worker failed.");
        }
      });
    });
  }
}

/**
 * update service worker.
 */
async function sw_update() {
  navigator.serviceWorker.getRegistration().then(function (reg) {
    reg.update().then(function (response) {
      if (response) {
        console.log("update of service worker succeeded.");
      } else {
        console.log("update of service worker failed.");
      }
    });
  });
}

/**
 * check for notification permission
 * if permission is default, ask for it
 */
function pn_checkPermission() {
  return new Promise((resolve, reject) => {
    if (Notification.permission === "denied") {
      return reject(new Error("Push messages are blocked."));
    }

    if (Notification.permission === "granted") {
      return resolve();
    }

    if (Notification.permission === "default") {
      return Notification.requestPermission().then((result) => {
        if (result !== "granted") {
          reject(new Error("Bad permission result"));
        } else {
          resolve();
        }
      });
    }

    return reject(new Error("Unknown permission"));
  });
}

/**
 * send notification subscription to the server for registration
 */
function pn_subscribe() {
  if (pn_isAvailable()) {
    var appPublicKey = encodeToUint8Array(strAppPublicKey);

    return pn_checkPermission()
      .then(() => navigator.serviceWorker.ready)
      .then((serviceWorkerRegistration) =>
        serviceWorkerRegistration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: appPublicKey,
        })
      )
      .then((subscription) => {
        // Subscription was successful
        // create subscription on your server
        return pn_sendSubscriptionToServer(subscription, "PUT");
      })
      .catch((e) => {
        if (Notification.permission === "denied") {
          // The user denied the notification permission which
          // means we failed to subscribe and the user will need
          // to manually change the notification permission to
          // subscribe to push messages
          console.warn("Notifications are denied by the user.");
        } else {
          // A problem occurred with the subscription; common reasons
          // include network errors or the user skipped the permission
          console.error("Impossible to subscribe to push notifications", e);
        }
      });
  } else {
    console.warn("Push notifications not available.");
  }
}

/**
 * send subscription to the server when updating
 */
function pn_updateSubscription() {
  navigator.serviceWorker.ready
    .then((serviceWorkerRegistration) =>
      serviceWorkerRegistration.pushManager.getSubscription()
    )
    .then((subscription) => {
      if (!subscription) {
        // Not subscribed to push
        pn_subscribe();
        return;
      }
      // Keep your server in sync with the latest endpoint
      return pn_sendSubscriptionToServer(subscription, "PUT");
    })
    .catch((e) => {
      console.error("Error when updating the subscription", e);
    });
}

function pn_getSubscription() {
  navigator.serviceWorker.ready
    .then((serviceWorkerRegistration) =>
      serviceWorkerRegistration.pushManager.getSubscription()
    )
    .then((subscription) =>
      pn_sendSubscriptionToServer(subscription, "POST").then((response) =>
        response
          .text()
          .then((text) => (document.getElementById("pn").innerHTML = text))
      )
    );
}

/**
 * unsubscribe to push notifications.
 * this does not reset the authorisations on the browser though
 */
async function pn_unsubscribe() {
  try {
    const serviceWorkerRegistration = await navigator.serviceWorker.ready;
    const subscription =
      await serviceWorkerRegistration.pushManager.getSubscription();

    if (!subscription) {
      console.log("No subscription object to unsubscribe.");
      return;
    }

    // Unsubscribe from the push service
    const response = await pn_sendSubscriptionToServer(subscription, "DELETE");

    if (response.ok) {
      await subscription.unsubscribe();
      console.log("Unsubscription successful.");
    } else {
      console.error("Failed to remove subscription from server.");
    }
  } catch (e) {
    console.error("Error when unsubscribing the user", e);
  }
}

/**
 * send subscription to the server using Fetch API
 * @param {PushSubscription} sub
 * @param {string} method
 * @returns {Promise<string>}
 */
async function pn_sendSubscriptionToServer(sub, method) {
  // stringify and parse again to add 'custom' property
  // otherwise added property will be ignored when stringify subscription direct to body
  console.log("Send Subscription command");
  var body = JSON.parse(JSON.stringify(sub));

  //debug
  //console.log(JSON.stringify(body));

  body.userAgent = navigator.userAgent;
  var fetchdata = {
    method: method,
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  };
  var response = await fetch(strSubscriberURL, fetchdata);

  // debug output
  //var cloned = response.clone();
  //console.log("Response: ", await cloned.text());

  return await response;
}

/**
 * Get the subscription informations
 */
function subscriptionsInformations() {
  var msg = "Secure context: ";
  msg += window.isSecureContext ? "true<br/>" : "false<br/>";
  msg += "Notification: ";
  msg += "Notification" in window ? "defined<br/>" : "not defined<br/>";
  msg += "PushManager: ";
  msg += "PushManager" in window ? "defined<br/>" : "not defined<br/>";
  msg += "serviceWorker: ";
  msg += "serviceWorker" in navigator ? "defined<br/>" : "not defined<br/>";
  msg += "Notification.permission: " + window.Notification.permission + "<br/>";

  document.getElementById("msg").innerHTML = msg;

  if (window.Notification.permission === "denied") {
    document.getElementById("subscribe").innerHTML =
      "Permission was denied in the past...";
  } else {
    sw_isSubscribed().then(function (subscribed) {
      if (subscribed) {
        document.getElementById("msg").innerHTML =
          "PUSH Notifications are subscribed<br/><br/>" + msg;
      } else {
        document.getElementById("msg").innerHTML =
          "PUSH Notifications not subscribed so far<br/><br/>" + msg;
      }
    });
  }
}

//Potential problem : this way, service worker and subscription are updated every time the page is reloaded
sw_register().then(() => pn_updateSubscription());
