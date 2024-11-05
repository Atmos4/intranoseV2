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
  if (!window.isSecureContext) {
    console.log("site have to run in secure context!");
    return false;
  }
  return (
    "serviceWorker" in navigator &&
    "PushManager" in window &&
    "Notification" in window
  );
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
  if (!(await sw_isSubscribed())) {
    return;
  }
  const reg = await navigator.serviceWorker.getRegistration();
  const response = await reg.unregister();

  if (response) {
    console.log("unregister service worker succeeded.");
  } else {
    console.log("unregister service worker failed.");
  }
}

/**
 * update service worker.
 */
async function sw_update() {
  const reg = await navigator.serviceWorker.getRegistration();
  const response = await reg.update();

  if (response) {
    console.log("update of service worker succeeded.");
  } else {
    console.log("update of service worker failed.");
  }
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
async function pn_subscribe() {
  if (!pn_isAvailable()) {
    console.warn("Push notifications not available.");
    return;
  }

  var appPublicKey = encodeToUint8Array(strAppPublicKey);

  return await pn_checkPermission()
    .then(() => navigator.serviceWorker.ready)
    .then((serviceWorkerRegistration) =>
      serviceWorkerRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: appPublicKey,
      })
    )
    .then((subscription) => {
      // create subscription on server
      return pn_sendSubscriptionToServer(subscription, "PUT");
    })
    .catch((e) => {
      if (Notification.permission === "denied") {
        console.warn("Notifications are denied by the user.");
      } else {
        console.error("Impossible to subscribe to push notifications", e);
      }
    });
}

/**
 * send subscription to the server when updating
 */
async function pn_updateSubscription() {
  try {
    const { pushManager } = await navigator.serviceWorker.ready;
    const subscription = await pushManager.getSubscription();
    if (!subscription) {
      return await pn_subscribe();
    }
    return await pn_sendSubscriptionToServer(subscription, "PUT");
  } catch (e) {
    console.error("Error when updating the subscription", e);
  }
}

async function pn_getSubscription() {
  const { pushManager } = await navigator.serviceWorker.ready;
  const subscription = await pushManager.getSubscription();
  const text = await pn_sendSubscriptionToServer(subscription, "POST").then(
    (r) => r.text()
  );
  document.getElementById("pn").innerHTML = text;
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
      console.log("No subscription found");
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
  console.log("Send Subscription command");
  var fetchdata = {
    method: method,
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(sub),
  };
  var response = await fetch(strSubscriberURL, fetchdata);
  return response;
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
sw_register().then(pn_updateSubscription);
