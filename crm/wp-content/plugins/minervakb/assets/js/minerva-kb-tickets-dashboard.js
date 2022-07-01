/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {

    var GLOBAL_DATA = window.MinervaKB;
    var DASHBOARD_DATA = window.MinervaTicketsDashboard;
    var ui = window.MinervaUI;
    var i18n = GLOBAL_DATA.i18n;

    var $dashboard = $('#tickets-dashboard');

    var graphDefaultColors = [
        'rgb(54,162,235)',
        'rgb(255,194,35)',
        'rgb(246,150,255)',
        'rgb(255,136,32)',
        'rgb(34,192,69)',
        'rgb(255,51,31)',
        'rgb(94,16,9)',
        'rgb(255,119,82)',
        'rgb(205,207,208)',
        'rgb(38,98,148)',
        'rgb(255,101,163)',
        'rgb(38,132,38)',
        'rgb(35,10,10)',
        'rgb(153,102,255)'
    ];

    /**
     * Renders circular chart
     * @param id
     * @param chartData
     */
    function renderDoughnutChart(id, chartData) {
        var el = document.getElementById(id);

        if (!el) {
            return;
        }

        var ctx = el.getContext('2d');

        var colors = chartData.colors.length ? chartData.colors : graphDefaultColors.slice(0, chartData.values.length);

        var chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.values,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                animation: {
                    animateRotate: false
                },
                legend: {
                    display: false
                },
                legendCallback: function (chart) {
                    var text = [];
                    var ds = chart.data.datasets[0];

                    text.push('<ul class="mkb-chart-legend">');

                    for (var i = 0; i < ds.data.length; i++) {
                        text.push('<li>');
                        text.push('<span class="mkb-chart-legend-item-color-box" style="background-color:' + ds.backgroundColor[i] + '">' +
                            '</span>' + chart.data.labels[i] + ' (' + ds.data[i] + ')');
                        text.push('</li>');
                    }

                    text.push('</ul>');

                    return text.join('');
                }
            }
        });

        $(el).after(chart.generateLegend());
    }

    /**
     * Renders line chart
     * @param id
     * @param chartData
     */
    function renderLineChart(id, chartData) {
        var el = document.getElementById(id);

        if (!el) {
            return;
        }

        var ctx = el.getContext('2d');

        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                // TODO; daily labels config
                labels: DASHBOARD_DATA.dailyChartRange,
                datasets: chartData.map(function(line, index) {
                    var color = line.color ? line.color : graphDefaultColors[index];

                    return {
                        label: line.label,
                        data: line.values,
                        borderColor: color,
                        pointBackgroundColor: color,
                        backgroundColor: 'rgba(0,0,0,0)',
                        borderWidth: 2,
                        lineTension: 0.3
                    }
                })
            },
            options: {
                animation: {
                    duration: 0
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            maxTicksLimit: 6,
                            callback: function(value) {
                                if (value % 1 === 0) {
                                    return value;
                                }
                            }
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                hover: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    display: false
                },
                legendCallback: function (chart) {
                    var text = [];
                    var datasets = chart.data.datasets;

                    text.push('<ul class="mkb-chart-legend">');

                    for (var i = 0; i < datasets.length; i++) {
                        text.push('<li>');
                        text.push('<span class="mkb-chart-legend-item-color-box" style="background-color:' + datasets[i].borderColor + '">' +
                            '</span>' + datasets[i].label);
                        text.push('</li>');
                    }

                    text.push('</ul>');

                    return text.join('');
                }
            }
        });

        $(el).after(chart.generateLegend());
    }

    /**
     * All charts render
     */
    function renderCharts() {
        renderDoughnutChart('mkb_chart_tickets_by_status', DASHBOARD_DATA.ticketsByStatus);
        renderDoughnutChart('mkb_chart_tickets_by_channel', DASHBOARD_DATA.ticketsByChannel);
        renderDoughnutChart('mkb_chart_tickets_by_type', DASHBOARD_DATA.ticketsByType);
        renderDoughnutChart('mkb_chart_tickets_by_priority', DASHBOARD_DATA.ticketsByPriority);
        renderDoughnutChart('mkb_chart_tickets_by_product', DASHBOARD_DATA.ticketsByProduct);
        renderDoughnutChart('mkb_chart_tickets_by_department', DASHBOARD_DATA.ticketsByDepartment);
        // TODO: real values
        renderDoughnutChart('mkb_chart_tickets_user_vs_guest', {
            labels: ['User Tickets', 'Guest Tickets'],
            values: [10, 18],
            colors: [
                'rgba(54,162,235)',
                'rgb(255,194,35)'
            ]
        });
        renderDoughnutChart('mkb_chart_tickets_by_agent', DASHBOARD_DATA.ticketsByAgent);

        /**
         * Daily charts
         */
        /**
         * TODO: keep consistent colors for each user (at least per render)
         */
        renderLineChart('mkb_chart_tickets_daily', DASHBOARD_DATA.ticketsDaily);
        renderLineChart('mkb_chart_new_tickets_per_agent_daily', DASHBOARD_DATA.ticketsDailyNewPerAgent);
        renderLineChart('mkb_chart_closed_tickets_per_agent_daily', DASHBOARD_DATA.ticketsDailyClosedPerAgent);
        renderLineChart('mkb_chart_reopened_tickets_per_agent_daily', DASHBOARD_DATA.ticketsDailyReopenedPerAgent);
    }

    function setupTicketElapsedTimeTickers() {
        // TODO: test this or read
        moment.locale($('html').attr('lang'));

        $('.js-mkb-human-readable-time').each(function(index, item) {
            var $item = $(item);
            var timestamp = item.dataset.timestamp * 1000;
            var ONCE_A_MINUTE = 1000 * 60;

            $item.html(moment.utc(timestamp).fromNow());

            setInterval(function() {
                $item.html(moment(timestamp).fromNow());
            }, ONCE_A_MINUTE);
        });
    }

    function setupElapsedTimeTicker($item) {
        var timestamp = $item.data('timestamp') * 1000;
        var ONCE_A_MINUTE = 1000 * 60;

        $item.html(moment.utc(timestamp).fromNow());

        setInterval(function() {
            $item.html(moment(timestamp).fromNow());
        }, ONCE_A_MINUTE);
    }

    function setupFilters() {
        var $filterForm = $('.js-mkb-tickets-dashboard-filters-form');

        if (!$filterForm) {
            return;
        }

        $filterForm.find('select').prop('disabled', false);

        $filterForm.on('submit', function() {
            $filterForm.find('select').filter(function(){ return !this.value; }).attr('disabled', 'disabled');
        });
    }

    function init() {
        setupFilters();
        renderCharts();
        setupTicketElapsedTimeTickers();

        toastr.options.positionClass = "toast-top-right";
        toastr.options.timeOut = 5000;
        toastr.options.showDuration = 200;
    }

    $(document).ready(init);
})(jQuery);