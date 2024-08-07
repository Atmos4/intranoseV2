// default Notification Title if not pushed by server
const strDefTitle = "Intranose";
// default Notification Icon if not pushed by server
const strDefIcon = "./assets/favicon/android-chrome-512x512.png";

/**
 * event listener to show notification
 * @param {PushEvent} event
 */
function pushNotification(event) {
  if (!(self.Notification && self.Notification.permission === "granted")) {
    return;
  }

  console.log(event.data);

  const data = event.data?.json() ?? {};
  const title = data.title || strDefTitle;
  const url = data.url || "www.intra.nose42.fr";
  const message = data.message;
  const icon = strDefIcon;

  var promise = self.registration.showNotification(title, {
    body: message,
    tag: "intranose-notification",
    icon: icon,
    badge: icon,
    data: {
      url: url,
      id: 1,
    },
  });
  event.waitUntil(promise);
}

/**
 * event listener to notification click
 * if URL passed, just open the window...
 * @param {NotificationClick} event
 */
function notificationClick(event) {
  console.log("notificationclick event: " + event);
  if (event.notification.data && event.notification.data.url) {
    const promise = clients.openWindow(event.notification.data.url);
    event.waitUntil(promise);
  }
  if (event.action !== "") {
    // add handler for user defined action here...
    // pnNotificationAction(event.action);
    console.log("notificationclick action: " + event.action);
  }
}

/**
 * event listener to notification close
 * ... if you want to do something for e.g. analytics
 * @param {NotificationClose} event
 */
function notificationClose(event) {
  console.log("notificationclose event: " + event);
}

function onInstall(event) {
  // The promise that skipWaiting() returns can be safely ignored.
  self.skipWaiting();

  // Perform any other actions required for your
  // service worker to install, potentially inside
  // of event.waitUntil();
}

// listen to incomming push notifications
self.addEventListener("push", pushNotification);
// listen to the click
self.addEventListener("notificationclick", notificationClick);
// notification was closed without further action
self.addEventListener("notificationclose", notificationClose);
// skip waiting when installing
self.addEventListener("install", onInstall);
