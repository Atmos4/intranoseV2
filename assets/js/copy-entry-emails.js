/**
 *
 * @param {string} email
 */
function copyEntryEmails(email) {
  if (email.trim() === "") {
    alert("Pas encore d'inscrit présent au déplacement ! 🥹");
  } else {
    navigator.clipboard.writeText(email);
    alert(
      "Emails copié dans le presse-papier 🤝 A copier dans les destinataires mail"
    );
  }
}
