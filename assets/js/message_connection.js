// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import {
  getFirestore,
  doc,
  setDoc,
  onSnapshot,
  serverTimestamp,
} from "https://www.gstatic.com/firebasejs/11.0.1/firebase-firestore.js";

// Firebase configuration
const firebaseConfig = {
  apiKey: window.firebaseApiKey,
  authDomain: "intranose-messages.firebaseapp.com",
  projectId: "intranose-messages",
  storageBucket: "intranose-messages.firebasestorage.app",
  messagingSenderId: "39611634215",
  appId: "1:39611634215:web:d445ed0bf90d6382013402",
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const db = getFirestore(app);

const convDoc = doc(db, "conversations", window.conversationId);

// Update message list when new message comes in
onSnapshot(convDoc, () => htmx.trigger(".messages", "messagesUpdated"));

// Update firebase document when sending a new message
document.body.addEventListener("newMessageSent", async () => {
  await setDoc(convDoc, { updated: serverTimestamp() });
});
