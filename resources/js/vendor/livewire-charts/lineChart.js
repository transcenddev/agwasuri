import { mergedOptionsWithJsonConfig } from "./helpers";

const lineChart = () => {
    return {
        chart: null,

        init() {
            setTimeout(() => {
                this.drawChart(this.$wire);
            }, 0);
        },

        drawChart(component) {
            // If chart already exists, update its series and categories in-place
            // instead of destroying/recreating the chart which causes flicker.
            if (this.chart) {
                try {
                    const title = component.get("lineChartModel.title");
                    const data = component.get("lineChartModel.data");

                    const series = [
                        {
                            name: title,
                            data: data.map((item) => {
                                if (item && typeof item === "object") {
                                    const x = item.x ?? item.title ?? null;
                                    const y = item.y ?? item.value ?? null;
                                    return x !== null
                                        ? { x: x, y: y }
                                        : { x: new Date().toISOString(), y: y };
                                }
                                return { x: new Date().toISOString(), y: item };
                            }),
                        },
                    ];

                    const categories =
                        (component.get("lineChartModel.xAxis.categories") || [])
                            .length > 0
                            ? component.get("lineChartModel.xAxis.categories")
                            : data.map(
                                  (item) =>
                                      (item && (item.x ?? item.title)) || null
                              );

                    // Update series without animation to prevent flicker
                    this.chart.updateSeries(series, false);

                    // Update x-axis categories if they changed (no animation)
                    try {
                        this.chart.updateOptions(
                            { xaxis: { categories: categories } },
                            false,
                            false
                        );
                    } catch (e) {
                        // ignore option update errors
                    }

                    return; // exit early, chart remains initialized
                } catch (err) {
                    // If update fails, fall through to re-create the chart
                    console.error(
                        "[lineChart] in-place update failed, recreating chart",
                        err
                    );
                }
            }

            const title = component.get("lineChartModel.title");
            const animated = component.get("lineChartModel.animated") || false;
            const dataLabels = component.get("lineChartModel.dataLabels") || {};
            const data = component.get("lineChartModel.data");
            const onPointClickEventName = component.get(
                "lineChartModel.onPointClickEventName"
            );
            const sparkline = component.get("lineChartModel.sparkline");
            const jsonConfig = component.get("lineChartModel.jsonConfig");

            const series = [
                {
                    name: title,
                    data: data.map((item) => {
                        if (item && typeof item === "object") {
                            const x = item.x ?? item.title ?? null;
                            const y = item.y ?? item.value ?? null;
                            return x !== null
                                ? { x: x, y: y }
                                : { x: new Date().toISOString(), y: y };
                        }
                        return { x: new Date().toISOString(), y: item };
                    }),
                },
            ];

            const categories =
                (component.get("lineChartModel.xAxis.categories") || [])
                    .length > 0
                    ? component.get("lineChartModel.xAxis.categories")
                    : data.map(
                          (item) => (item && (item.x ?? item.title)) || null
                      );

            const options = {
                series: series,

                chart: {
                    type: "line",
                    height: "100%",

                    ...sparkline,

                    zoom: { enabled: false },

                    toolbar: { show: false },

                    animations: {
                        enabled: animated,
                        dynamicAnimation: {
                            enabled: false,
                            speed: 350,
                        },
                    },

                    events: {
                        markerClick: function (
                            event,
                            chartContext,
                            { dataPointIndex }
                        ) {
                            if (!onPointClickEventName) {
                                return;
                            }

                            const point = data[dataPointIndex];
                            component.call("onPointClick", point);
                        },
                    },
                },

                dataLabels: dataLabels,

                // Immutable stroke settings to preserve line appearance across updateSeries
                stroke: {
                    show: true,
                    curve: "smooth",
                    width: 2,
                    lineCap: "round",
                },

                // Hide markers by default for a clean line view
                markers: {
                    size: 0,
                },

                theme: component.get("lineChartModel.theme") || {},

                title: {
                    text: title,
                    align: "center",
                },

                xaxis: {
                    // Force datetime mode so points maintain numeric x values
                    type: "datetime",
                    labels: component.get("lineChartModel.xAxis.labels"),
                    categories: categories,
                },

                yaxis: component.get("lineChartModel.yAxis") || {},

                annotations: {
                    points: component
                        .get("lineChartModel.markers")
                        .map((item) => {
                            return {
                                x: item.title,
                                y: item.value,
                                marker: {
                                    size: 6,
                                    fillColor: "#fff",
                                    strokeColor: item.strokeColor,
                                    radius: 2,
                                },
                                label: {
                                    offsetY: 0,
                                    style: {
                                        color: item.textColor,
                                        background: item.textBackgroundColor,
                                    },
                                    text: item.text || "",
                                },
                            };
                        }),
                },

                tooltip: {
                    y: {
                        formatter: function (value, series) {
                            return (
                                data[series.dataPointIndex].extras.tooltip ||
                                value
                            );
                        },
                    },
                },
            };

            this.chart = new ApexCharts(
                this.$refs.container,
                mergedOptionsWithJsonConfig(options, jsonConfig)
            );
            this.chart.render();
        },
    };
};

export default lineChart;
