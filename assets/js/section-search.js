function searchSection(inputId, sectionId) {
  // Declare variables
  let input, filter, section, name_a;
  input = document.getElementById(inputId);
  filter = input.value.toUpperCase();
  section = document.getElementById(sectionId);
  articles = section.getElementsByTagName("article");
  for (i = 0; i < articles.length; i++) {
    name_a = articles[i].getElementsByTagName("a")[0];
    txtValue = name_a.textContent || name_a.innerText;
    if (txtValue.toUpperCase().indexOf(filter) > -1) {
      console.log(txtValue);
      articles[i].classList.add("card");
      articles[i].classList.remove("hidden");
      shouldTraverse = false;
    } else {
      articles[i].classList.remove("card");
      articles[i].classList.add("hidden");
    }
  }
}
