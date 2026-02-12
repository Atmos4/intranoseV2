function start_intro() {
  const tabGroup = document.querySelector("sl-tab-group");
  const intro = introJs();

  intro.onbeforechange((element) => {
    // Find the closest tab-panel that contains the element
    const tabPanel = element.closest("sl-tab-panel");
    if (tabPanel) {
      tabGroup.show(tabPanel.name);
    }
  });

  intro.start();
}
