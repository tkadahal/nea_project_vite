import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/fullcalendar.css", // Add FullCalendar CSS
                "resources/js/app.js",
                "resources/js/taskCalendar.js", // Add FullCalendar JS
                "resources/js/departmentLoader.js",
                "resources/js/formDependencies.js",
                // "resources/js/projectManagerLoader.js",
                "resources/js/projectBudget.js",
                "resources/js/projectLoader.js",
                "resources/js/contract.js",
                // "resources/js/userProject.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
