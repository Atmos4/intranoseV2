function selectTable() {
  const table = document.querySelector("table");
  const range = document.createRange();
  range.selectNodeContents(table);
  const selection = window.getSelection();
  selection.removeAllRanges();
  selection.addRange(range);
  const text = selection.toString();
  navigator.clipboard.writeText(text);
  selection.removeAllRanges();
  alert(
    "Tableau copié dans le presse-papier 👌 À coller dans un tableur (Sheets ou Excel)"
  );
}
