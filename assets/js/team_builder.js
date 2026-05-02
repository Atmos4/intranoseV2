(function () {
  function init() {
    var container = document.getElementById("teams-container");
    if (!container || container.dataset.tbInit) return;
    container.dataset.tbInit = "1";

    var teamCount = parseInt(container.dataset.teamCount, 10);

    var canEdit = container.dataset.canEdit === "true";
    var eventId = container.dataset.eventId;
    var poolId = container.dataset.poolId;

    function refreshUserAssignments() {
      document.querySelectorAll(".user-drag-item").forEach(function (item) {
        var uid = item.dataset.userId;
        var inTeam = document.querySelector(
          '.team-member-chip[data-user-id="' + uid + '"]',
        );
        item.classList.toggle("user-in-team", !!inTeam);
      });
    }

    function reloadTeamStructure(col) {
      var teamIndex = col.dataset.teamIndex;
      var slotsContainer =
        col.querySelector(".team-slots-container") ||
        col.querySelector(".team-drop-zone");
      if (!slotsContainer) return;

      // Collect current members in their current positions
      var memberIds = [];
      if (col.querySelector(".team-slots-container")) {
        // Slot-based: collect in slot order
        col.querySelectorAll(".relay-slot-drop").forEach(function (drop) {
          var chip = drop.querySelector(".team-member-chip");
          memberIds.push(chip ? chip.dataset.userId : "");
        });
      } else {
        // Free zone: collect in DOM order
        col
          .querySelectorAll(".team-drop-zone .team-member-chip")
          .forEach(function (chip) {
            memberIds.push(chip.dataset.userId);
          });
      }

      var formatSelect = col.querySelector(
        'select[name="team_' + teamIndex + '_relay_format"]',
      );
      var relayFormat = formatSelect ? formatSelect.value : "";

      var postData = {
        team_index: teamIndex,
        can_edit: canEdit,
      };
      postData["team_" + teamIndex + "_relay_format"] = relayFormat;
      postData["team_" + teamIndex + "_members"] = JSON.stringify(memberIds);

      // Reload slots; _slots_component will OOB-swap the composition block too
      htmx.ajax(
        "POST",
        "/evenements/" + eventId + "/pool/" + poolId + "/team_slots",
        {
          target: "#slots-" + teamIndex,
          swap: "outerHTML",
          values: postData,
        },
      );
    }

    // --- Drag & Drop (editor mode) ---
    if (canEdit) {
      var draggedChip = null;
      var dragSourceSlot = null;
      var dragFromHandle = false;

      // Track mousedown on drag handle
      container.addEventListener("mousedown", function (e) {
        dragFromHandle = !!e.target.closest(".member-drag-handle");
      });

      // User panel dragstart
      document
        .getElementById("users-container")
        .addEventListener("dragstart", function (e) {
          var item = e.target.closest(".user-drag-item");
          if (!item) return;
          e.dataTransfer.setData(
            "text/plain",
            JSON.stringify({
              id: item.dataset.userId,
              name: item.dataset.userName,
              picture: item.dataset.userPicture,
              category: item.dataset.userCategory || "",
            }),
          );
          e.dataTransfer.effectAllowed = "move";
        });

      // Member chip dragstart
      container.addEventListener("dragstart", function (e) {
        var chip = e.target.closest(".team-member-chip");
        if (!chip) return;
        var slotDrop = chip.closest(".relay-slot-drop");
        if (slotDrop) {
          draggedChip = chip;
          dragSourceSlot = slotDrop;
          chip.classList.add("member-dragging");
          e.dataTransfer.effectAllowed = "move";
          e.dataTransfer.setData("text/member-swap", chip.dataset.userId);
        } else {
          if (!dragFromHandle) {
            e.preventDefault();
            return;
          }
          draggedChip = chip;
          dragSourceSlot = null;
          chip.classList.add("member-dragging");
          e.dataTransfer.effectAllowed = "move";
          e.dataTransfer.setData("text/member-reorder", chip.dataset.userId);
        }
      });

      // Dragover
      container.addEventListener("dragover", function (e) {
        var slotDrop = e.target.closest(".relay-slot-drop");
        if (slotDrop) {
          e.preventDefault();
          e.dataTransfer.dropEffect = "move";
          slotDrop.classList.add("drag-over");
          return;
        }
        var zone = e.target.closest(".team-drop-zone");
        if (zone) {
          if (draggedChip && dragSourceSlot) return;
          if (draggedChip) {
            var target = e.target.closest(".team-member-chip");
            if (!target || target === draggedChip || !zone.contains(target))
              return;
            e.preventDefault();
            e.dataTransfer.dropEffect = "move";
            var rect = target.getBoundingClientRect();
            if (e.clientY > rect.top + rect.height / 2) {
              target.parentNode.insertBefore(draggedChip, target.nextSibling);
            } else {
              target.parentNode.insertBefore(draggedChip, target);
            }
          } else {
            e.preventDefault();
            e.dataTransfer.dropEffect = "move";
            zone.classList.add("drag-over");
          }
        }
      });

      // Dragleave
      container.addEventListener("dragleave", function (e) {
        var slotDrop = e.target.closest(".relay-slot-drop");
        if (slotDrop && !slotDrop.contains(e.relatedTarget)) {
          slotDrop.classList.remove("drag-over");
        }
        var zone = e.target.closest(".team-drop-zone");
        if (zone && !zone.contains(e.relatedTarget)) {
          zone.classList.remove("drag-over");
        }
      });

      // Drop
      container.addEventListener("drop", function (e) {
        var slotDrop = e.target.closest(".relay-slot-drop");
        if (slotDrop) {
          slotDrop.classList.remove("drag-over");

          // Slot swap within/between teams
          if (draggedChip && dragSourceSlot) {
            e.preventDefault();
            if (dragSourceSlot === slotDrop) return;

            var sourceChip = dragSourceSlot.querySelector(".team-member-chip");
            var targetChip = slotDrop.querySelector(".team-member-chip");
            var sourceCol = dragSourceSlot.closest(".team-column");
            var targetCol = slotDrop.closest(".team-column");

            // Swap chips in DOM (optimistic update)
            if (sourceChip) sourceChip.remove();
            if (targetChip) targetChip.remove();
            if (sourceChip)
              dragSourceSlot.appendChild(
                targetChip || document.createElement("span"),
              );
            if (targetChip)
              slotDrop.appendChild(
                sourceChip || document.createElement("span"),
              );

            setTimeout(function () {
              reloadTeamStructure(sourceCol);
              if (targetCol !== sourceCol) reloadTeamStructure(targetCol);
            }, 0);
            return;
          }

          // Drop from user panel
          if (e.dataTransfer.types.indexOf("text/plain") === -1) return;
          e.preventDefault();
          var data = JSON.parse(e.dataTransfer.getData("text/plain"));
          var col = slotDrop.closest(".team-column");

          // Remove from existing location
          document
            .querySelectorAll(
              '.team-member-chip[data-user-id="' + data.id + '"]',
            )
            .forEach(function (chip) {
              var oldCol = chip.closest(".team-column");
              chip.remove();
              if (oldCol && oldCol !== col) {
                setTimeout(function () {
                  reloadTeamStructure(oldCol);
                }, 0);
              }
            });

          // Create chip optimistically
          var existing = slotDrop.querySelector(".team-member-chip");
          if (existing) existing.remove();

          var newChip = document.createElement("div");
          newChip.className = "team-member-chip";
          newChip.draggable = true;
          newChip.dataset.userId = data.id;
          newChip.dataset.userCategory = data.category || "";
          newChip.innerHTML =
            '<img src="' +
            data.picture +
            '" alt=""><span>' +
            data.name +
            "</span>" +
            (data.category
              ? '<small class="user-category-badge">' +
                data.category +
                "</small>"
              : "") +
            '<button type="button" onclick="removeMember(this, ' +
            data.id +
            ')">&times;</button>';
          slotDrop.appendChild(newChip);

          setTimeout(function () {
            reloadTeamStructure(col);
          }, 0);
          return;
        }

        // Drop zone (non-relay)
        var zone = e.target.closest(".team-drop-zone");
        if (!zone) return;
        if (e.dataTransfer.types.indexOf("text/member-reorder") !== -1) return;
        if (draggedChip) return;
        e.preventDefault();
        zone.classList.remove("drag-over");

        var data = JSON.parse(e.dataTransfer.getData("text/plain"));
        document
          .querySelectorAll('.team-member-chip[data-user-id="' + data.id + '"]')
          .forEach(function (chip) {
            var oldCol = chip.closest(".team-column");
            chip.remove();
            if (oldCol) {
              setTimeout(function () {
                reloadTeamStructure(oldCol);
              }, 0);
            }
          });

        var hint = zone.querySelector(".drop-hint");
        if (hint) hint.remove();

        var newChip = document.createElement("div");
        newChip.className = "team-member-chip";
        newChip.draggable = true;
        newChip.dataset.userId = data.id;
        newChip.dataset.userCategory = data.category || "";
        newChip.innerHTML =
          '<div class="member-drag-handle" title="Glisser pour réordonner"><i class="fa fa-grip-vertical"></i></div>' +
          '<img src="' +
          data.picture +
          '" alt=""><span>' +
          data.name +
          "</span>" +
          (data.category
            ? '<small class="user-category-badge">' + data.category + "</small>"
            : "") +
          '<button type="button" onclick="removeMember(this, ' +
          data.id +
          ')">&times;</button>';
        zone.appendChild(newChip);

        var col = zone.closest(".team-column");
        setTimeout(function () {
          reloadTeamStructure(col);
        }, 0);
      });

      // Dragend
      container.addEventListener("dragend", function (e) {
        if (!draggedChip) return;
        draggedChip.classList.remove("member-dragging");

        // Free zone reorder
        if (!dragSourceSlot) {
          var zone = draggedChip.closest(".team-drop-zone");
          if (zone) {
            var col = zone.closest(".team-column");
            setTimeout(function () {
              reloadTeamStructure(col);
            }, 0);
          }
        }

        draggedChip = null;
        dragSourceSlot = null;
      });
    } // end canEdit

    // --- Team management ---
    window.addTeam = function () {
      var wrapper = document.createElement("div");
      wrapper.id = "team-wrapper-" + teamCount;
      wrapper.setAttribute(
        "hx-post",
        "/evenements/" + eventId + "/pool/" + poolId + "/team_form",
      );
      wrapper.setAttribute("hx-trigger", "load");
      wrapper.setAttribute("hx-swap", "outerHTML");
      wrapper.setAttribute(
        "hx-vals",
        JSON.stringify({
          action: teamCount,
          form_values: null,
        }),
      );

      container.insertBefore(
        wrapper,
        container.querySelector(".team-add-column"),
      );
      htmx.process(wrapper);
      teamCount++;
      document.getElementById("team-count").value = teamCount;
    };

    window.removeTeam = function (index) {
      var wrapper = document.getElementById("team-wrapper-" + index);
      if (!wrapper) return;
      wrapper.remove();
      teamCount--;
      document.getElementById("team-count").value = teamCount;
      refreshUserAssignments();
    };

    window.removeMember = function (btn, userId) {
      var chip = btn.closest(".team-member-chip");
      var col = chip.closest(".team-column");
      chip.remove();
      setTimeout(function () {
        reloadTeamStructure(col);
        refreshUserAssignments();
      }, 0);
    };

    // --- HTMX after settle ---
    document.body.addEventListener("htmx:afterSettle", function () {
      refreshUserAssignments();
    });

    // --- Init ---
    refreshUserAssignments();
  }
  init();
  document.body.addEventListener("htmx:afterSettle", init);
})();
