function searchTable(tableId) {
  // Declare variables
  let searchFilter,
    activeValue,
    inactiveValue,
    table,
    tr,
    td,
    i,
    j,
    txtValue,
    shouldTraverse;
  searchFilter = document.getElementById("search-users").value.toUpperCase();
  activeValue = document.getElementById("active").checked;
  inactiveValue = document.getElementById("inactive").checked;
  table = document.getElementById(tableId).getElementsByTagName("tbody")[0];
  tr = table.getElementsByTagName("tr");

  // Loop through all table rows, and hide those who don't match the search query
  for (i = 0; i < tr.length; i++) {
    var tds = tr[i].getElementsByTagName("td");
    shouldTraverse = true;
    for (j = 0; j < tds.length; j++) {
      td = tds[j];
      if (shouldTraverse && td) {
        txtValue = td.textContent || td.innerText;
        if (txtValue.toUpperCase().indexOf(searchFilter) > -1) {
          tr[i].classList.remove("hidden");
          shouldTraverse = false;
        } else {
          tr[i].classList.add("hidden");
        }
      }
    }
    if (
      (!activeValue && tds[4].textContent == 1) ||
      (!inactiveValue && tds[4].textContent == 0)
    ) {
      tr[i].classList.add("hidden");
    }
  }
}
