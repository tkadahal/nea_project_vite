import "./bootstrap";
import $ from "jquery";
import { Editor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import toastr from "toastr";

window.jQuery = window.$ = $;
window.toastr = toastr;

import Gantt from "frappe-gantt";
window.Gantt = Gantt;

$(document).ready(function () {
    console.log("jQuery version:", $.fn.jquery);

    // Set Toastr options
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 5000,
    };

    console.log("Toastr initialized:", toastr);

    // Initialize Tiptap editor (unchanged)
    $("[data-tiptap-editor]").each(function () {
        const $container = $(this);
        const $editorContent = $container.find('[ref="editorContent"]');
        const editor = new Editor({
            element: $container[0],
            extensions: [StarterKit],
            content: "",
            onUpdate: ({ editor }) => {
                if ($editorContent.length) {
                    $editorContent.html(editor.getHTML());
                }
                $container.trigger("input", editor.getHTML());
                $("#editor-content").val(editor.getHTML());
            },
        });
        $container.data("tiptap-editor", editor);
        $(window).on("beforeunload", function () {
            if (editor) {
                editor.destroy();
            }
        });
    });

    // Profile dropdown (unchanged)
    $(".js-profile-dropdown").each(function () {
        const $container = $(this);
        const $dropdown = $container.find(".js-dropdown-menu");

        $container.find(".js-toggle-dropdown").on("click", function (e) {
            e.preventDefault();
            $dropdown.toggleClass("hidden");
        });

        $(document).on("click", function (e) {
            if (
                !$container.is(e.target) &&
                $container.has(e.target).length === 0
            ) {
                $dropdown.addClass("hidden");
            }
        });
    });

    // Sidebar functionality
    function updateSidebarState(isOpen) {
        const $sidebar = $(".js-sidebar");
        const $labels = $(".js-submenu-label, .js-link-label");
        const $chevrons = $(".js-chevron");

        if (isOpen) {
            $sidebar.removeClass("hidden").addClass("w-full md:w-64");
            $labels.removeClass("opacity-0").addClass("opacity-100");
            $chevrons.removeClass("opacity-0").addClass("opacity-100");
            // Restore previously open submenus
            $(".js-submenu").filter(":visible").show();
        } else {
            $sidebar.addClass("hidden").removeClass("w-full md:w-64");
            $labels.removeClass("opacity-100").addClass("opacity-0");
            $chevrons.removeClass("opacity-100").addClass("opacity-0");
            $(".js-submenu").addClass("hidden");
            $(".js-chevron").removeClass("rotate-90");
        }

        $sidebar.attr("data-open", isOpen);
        localStorage.setItem("sidebarState", isOpen ? "open" : "closed");
        console.log("Sidebar state:", isOpen ? "open" : "closed");

        // Dispatch sidebarToggle event
        const event = new Event("sidebarToggle");
        document.body.dispatchEvent(event);
    }

    // Initialize sidebar state
    const savedSidebarState = localStorage.getItem("sidebarState");
    if (savedSidebarState === "open") {
        updateSidebarState(true);
    } else {
        updateSidebarState(false);
    }

    // Toggle sidebar
    $(".js-toggle-sidebar").on("click", function (e) {
        e.preventDefault();
        const isOpen = $(".js-sidebar").attr("data-open") === "true";
        updateSidebarState(!isOpen);
    });

    // Collapsible menu
    $(".js-collapsible-menu").each(function () {
        const $menu = $(this);
        const $toggle = $menu.find(".js-toggle-submenu");
        const $submenu = $menu.find(".js-submenu");
        const $chevron = $menu.find(".js-chevron");

        $toggle.on("click", function (e) {
            e.preventDefault();
            const isSidebarOpen = $(".js-sidebar").attr("data-open") === "true";
            if (!isSidebarOpen) {
                // Open sidebar and submenu
                updateSidebarState(true);
                $submenu.removeClass("hidden");
                $chevron.addClass("rotate-90");
                console.log("Sidebar expanded and submenu opened");
            } else {
                // Toggle submenu
                $submenu.toggleClass("hidden");
                $chevron.toggleClass("rotate-90");
                console.log(
                    "Submenu toggled:",
                    $submenu.hasClass("hidden") ? "closed" : "open",
                );
            }
        });
    });

    // Handle window resize
    $(window).on("resize", function () {
        const isOpen = $(".js-sidebar").attr("data-open") === "true";
        if (window.innerWidth < 768 && isOpen) {
            updateSidebarState(false);
        } else if (window.innerWidth >= 768 && !isOpen) {
            updateSidebarState(true);
        }
    });

    // Dropdown and accordion logic (unchanged)
    const emailInput = $('input[name="email"]').val();
    console.log("Email input value on load:", emailInput);

    $(".dropdown-toggle").on("click", function () {
        const dropdownId = $(this).data("dropdown");
        console.log("Toggling dropdown:", dropdownId);
        $("#" + dropdownId).toggleClass("hidden");
    });

    $(document).on("click", function (event) {
        if (
            !$(event.target).closest(".dropdown-toggle").length &&
            !$(event.target).closest('[id^="dropdown-"]').length
        ) {
            $('[id^="dropdown-"]').addClass("hidden");
        }
    });

    $(".accordion-toggle").on("click", function () {
        const accordionId = $(this).data("accordion");
        const $accordionContent = $("#" + accordionId);
        const $icon = $(this).find(".accordion-icon");

        $accordionContent.toggleClass("hidden");
        const isExpanded = !$accordionContent.hasClass("hidden");
        $(this).attr("aria-expanded", isExpanded);
        $icon.toggleClass("rotate-180", isExpanded);
    });
});
