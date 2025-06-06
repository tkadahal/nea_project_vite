import "./bootstrap";
import $ from "jquery";
import { Editor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";

window.jQuery = window.$ = $;

$(document).ready(function () {
    console.log("jQuery version:", $.fn.jquery);

    // Initialize Tiptap editor
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

    // Profile dropdown
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
            $sidebar.removeClass("hidden md:w-16").addClass("w-full md:w-64");
            $labels.removeClass("opacity-0").addClass("opacity-100");
            $chevrons.removeClass("opacity-0").addClass("opacity-100");
            $(".js-submenu").filter(":visible").show();
        } else {
            $sidebar
                .removeClass("w-full md:w-64")
                .addClass("hidden md:block md:w-16");
            $labels.removeClass("opacity-100").addClass("opacity-0");
            $chevrons.removeClass("opacity-100").addClass("opacity-0");
            $(".js-submenu").hide();
        }

        $sidebar.attr("data-open", isOpen);
        localStorage.setItem("sidebarState", isOpen ? "open" : "closed");
        console.log("Sidebar state:", isOpen ? "open" : "closed");
    }

    // Initialize sidebar based on screen size or saved state
    const savedSidebarState = localStorage.getItem("sidebarState");
    if (savedSidebarState === "open") {
        updateSidebarState(true);
    } else if (savedSidebarState === "closed") {
        updateSidebarState(false);
    } else {
        // No saved state: use screen size
        if (window.innerWidth >= 768) {
            updateSidebarState(true); // Expanded on md+
        } else {
            updateSidebarState(false); // Collapsed on small
        }
    }

    // Toggle sidebar manually
    $(".js-toggle-sidebar").on("click", function (e) {
        e.preventDefault();
        const isOpen = $(".js-sidebar").attr("data-open") === "true";
        updateSidebarState(!isOpen);
    });

    $(".js-collapsible-menu").each(function () {
        const $menu = $(this);
        const $toggle = $menu.find(".js-toggle-submenu");
        const $submenu = $menu.find(".js-submenu");
        const $chevron = $menu.find(".js-chevron");

        $toggle.on("click", function (e) {
            e.preventDefault();
            const isSidebarOpen = $(".js-sidebar").attr("data-open") === "true";
            if (isSidebarOpen) {
                $submenu.toggleClass("hidden");
                $chevron.toggleClass("rotate-90");
                console.log(
                    "Submenu toggled:",
                    $submenu.hasClass("hidden") ? "closed" : "open",
                );
            } else {
                updateSidebarState(true);
                $submenu.removeClass("hidden");
                $chevron.addClass("rotate-90");
                console.log("Sidebar expanded and submenu opened");
            }
        });
    });

    // Resize listener (optional: to dynamically adjust if user resizes window)
    $(window).on("resize", function () {
        const currentState = $(".js-sidebar").attr("data-open");
        if (window.innerWidth < 768 && currentState === "true") {
            updateSidebarState(false);
        } else if (window.innerWidth >= 768 && currentState === "false") {
            updateSidebarState(true);
        }
    });

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
