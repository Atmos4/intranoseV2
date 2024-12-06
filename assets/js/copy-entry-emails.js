/**
 *
 * @param {string} email
 */
function copyEntryEmails(email) {
  navigator.clipboard.writeText(email);
  alert(
    "Emails copié dans le presse-papier 🤝 A copier dans les destinataires mail"
  );
}
