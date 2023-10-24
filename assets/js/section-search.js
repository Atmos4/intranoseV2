function searchSection(inputId, sectionId) {
  // Declare variables
  let input, filter, section, name_a, articles;
  input = document.getElementById(inputId);
  filter = input.value.toUpperCase();
  section = document.getElementById(sectionId);
  articles = section.getElementsByClassName("toggleWrapper");
  for (i = 0; i < articles.length; i++) {
    name_a = articles[i].getElementsByTagName("a")[0];
    txtValue = name_a.textContent || name_a.innerText;
    if (txtValue.toUpperCase().indexOf(filter) > -1) {
      articles[i].classList.remove("hidden");
    } else {
      articles[i].classList.add("hidden");
    }
  }
}
