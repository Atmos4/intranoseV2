(function () {
  var CHIP_GROUP = "team-members";

  // Builds/rebuilds a chip's contents on an existing node, in place, so
  // callers that need to preserve the node's identity (e.g. the node
  // Sortable is actively dragging) can restyle it without a swap.
  function fillChipContents(chip, data) {
    chip.innerHTML = "";
    chip.dataset.userId = data.id;
    chip.dataset.userCategory = data.category || "";

    var img = document.createElement("img");
    img.src = data.picture;
    img.alt = "";
    chip.appendChild(img);

    var name = document.createElement("span");
    name.textContent = data.name;
    chip.appendChild(name);

    if (data.category) {
      var badge = document.createElement("small");
      badge.className = "user-category-badge";
      badge.textContent = data.category;
      chip.appendChild(badge);
    }

    var removeBtn = document.createElement("button");
    removeBtn.type = "button";
    removeBtn.textContent = "×";
    removeBtn.addEventListener("click", function () {
      window.removeMember(removeBtn, data.id);
    });
    chip.appendChild(removeBtn);

    return chip;
  }

  function clearDragOver() {
    document.querySelectorAll(".drag-over").forEach(function (el) {
      el.classList.remove("drag-over");
    });
  }

  // A raw item from the user panel needs to become a real team-member chip.
  // Restyled in place (not via replaceWith) so it can also be called on
  // the item the instant a drag starts, while Sortable is still holding
  // a reference to that exact node and moving it around the DOM.
  function convertDragItemToChip(item) {
    if (!item.classList.contains("user-drag-item")) return item;
    var data = {
      id: item.dataset.userId,
      name: item.dataset.userName,
      picture: item.dataset.userPicture,
      category: item.dataset.userCategory || "",
    };
    item.classList.remove("user-drag-item");
    item.classList.add("team-member-chip");
    return fillChipContents(item, data);
  }

  // Relay slots hold a single member: evict/swap the surplus chip
  function evictSurplusFromSlot(toEl, keptItem, fromEl) {
    if (!toEl.classList.contains("relay-slot-drop")) return;
    var chips = toEl.querySelectorAll(".team-member-chip");
    if (chips.length <= 1) return;

    var surplus = null;
    chips.forEach(function (c) {
      if (c !== keptItem) surplus = c;
    });
    if (!surplus) return;

    var canReturnToSource =
      fromEl !== toEl &&
      (fromEl.classList.contains("relay-slot-drop") ||
        fromEl.classList.contains("team-drop-zone"));
    if (canReturnToSource) {
      fromEl.appendChild(surplus);
    } else {
      surplus.remove();
    }
  }

  // A member can only be in one team/slot at a time
  function removeDuplicateChips(item, onRemoved) {
    document
      .querySelectorAll(
        '.team-member-chip[data-user-id="' + item.dataset.userId + '"]',
      )
      .forEach(function (chip) {
        if (chip === item) return;
        onRemoved(chip);
        chip.remove();
      });
  }

  function init() {
    var container = document.getElementById("teams-container");
    if (!container) {
      return;
    }

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
      if (!col) return;
      var teamIndex = col.dataset.teamIndex;
      var slotsContainer =
        col.querySelector(".team-slots-container") ||
        col.querySelector(".team-drop-zone");
      if (!slotsContainer) {
        return;
      }

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

    // --- Drag & Drop (editor mode), powered by Sortable.js ---
    function handleSortEnd(evt) {
      clearDragOver();
      var item = evt.item;
      if (!item.isConnected) {
        return; // dropped on an invalid target and discarded
      }

      var fromEl = evt.from;
      var toEl = evt.to;

      // Dropped back onto the source panel
      if (toEl.id === "users-container") {
        return;
      }

      item = convertDragItemToChip(item);

      // Pure cosmetic : don't show back the hint when dropped
      var slotHint = toEl.querySelector(".slot-drop-hint");
      if (slotHint) slotHint.remove();
      var hint = toEl.querySelector(".drop-hint");
      if (hint) hint.remove();

      var affectedCols = {};
      function markCol(el) {
        var col = el && el.closest && el.closest(".team-column");
        if (col) affectedCols[col.dataset.teamIndex] = col;
      }
      markCol(toEl);
      if (fromEl.id !== "users-container") markCol(fromEl);

      evictSurplusFromSlot(toEl, item, fromEl);
      removeDuplicateChips(item, markCol);

      Object.keys(affectedCols).forEach(function (idx) {
        reloadTeamStructure(affectedCols[idx]);
      });
      refreshUserAssignments();
    }

    function initSortables() {
      if (typeof Sortable === "undefined") return;

      var usersContainer = document.getElementById("users-container");
      if (usersContainer && !usersContainer._sortable) {
        usersContainer._sortable = Sortable.create(usersContainer, {
          group: { name: CHIP_GROUP, pull: "clone", put: false },
          sort: false,
          filter: "button",
          preventOnFilter: true,
          animation: 150,
          // Restyle the dragged item into chip form the instant the drag
          // starts (evt.item is the node Sortable actually moves through
          // the DOM as you hover over slots — a separate hidden clone is
          // left behind in the users panel to fill the gap), so it has
          // its final (narrower) shape for the whole drag instead of
          // overflowing the wide users-panel layout until it's dropped.
          onStart: function (evt) {
            convertDragItemToChip(evt.item);
          },
          onEnd: handleSortEnd,
        });
      }

      // Highlighting is driven by native dragenter, not Sortable's onMove:
      // onMove is skipped by Sortable's internal "revert" fast path when a
      // dragged item comes back over its own origin slot, which left that
      // slot's previous drag-over target stuck highlighted. dragenter fires
      // off the real cursor position, so it can't be skipped that way.
      container
        .querySelectorAll(".relay-slot-drop, .team-drop-zone")
        .forEach(function (el) {
          if (el._sortable) return;
          var isFreeZone = el.classList.contains("team-drop-zone");
          el._sortable = Sortable.create(el, {
            group: { name: CHIP_GROUP, pull: true, put: true },
            sort: isFreeZone,
            draggable: ".team-member-chip",
            filter: "button",
            preventOnFilter: true,
            animation: 150,
            ghostClass: "member-dragging",
            onEnd: handleSortEnd,
          });
          el.addEventListener("dragenter", function () {
            clearDragOver();
            el.classList.add("drag-over");
          });
        });
    }

    // --- Team management ---
    function oneTimeSetup() {
      var teamCount = parseInt(container.dataset.teamCount, 10);

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
        if (!wrapper) {
          return;
        }
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
    }

    refreshUserAssignments();
    if (canEdit) initSortables();

    if (container.dataset.tbInit) {
      return;
    }
    container.dataset.tbInit = "1";
    oneTimeSetup();
  }

  init();
  document.body.addEventListener("htmx:afterSettle", function () {
    init();
  });
})();
