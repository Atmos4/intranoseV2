function selectTable() {
  const table = document.querySelector("table");
  const range = document.createRange();
  range.selectNodeContents(table);
  const selection = window.getSelection();
  selection.removeAllRanges();
  selection.addRange(range);
  const text = selection.toString().replace(/\t/g, "    ");
  navigator.clipboard.writeText(text);
  selection.removeAllRanges();
}
