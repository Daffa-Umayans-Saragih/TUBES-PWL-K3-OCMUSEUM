import "../css/admin.css";

const CHART_JS_SRC = "https://cdn.jsdelivr.net/npm/chart.js";

function loadChartJs() {
    if (window.Chart) {
        return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.src = CHART_JS_SRC;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error("Failed to load Chart.js"));
        document.head.appendChild(script);
    });
}

function initAdminPageScripts() {
    if (document.querySelector(".payment-dashboard")) {
        import("./admin/payment/index.js");
    }

    if (document.querySelector(".ticket-analytics-dashboard")) {
        loadChartJs().then(() => {
            import("./admin/ticket-analytics/index.js");
        });
    }
}

document.addEventListener("DOMContentLoaded", initAdminPageScripts);
