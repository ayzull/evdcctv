{{-- resources\views\anpr\analytic.blade.php --}}
<div class="container mt-5">
    <div class="text-center my-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Daily</h5>
                        <input type="date" id="daily-date-picker" class="form-control mb-3"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}">
                        <p class="card-text" id="daily-total">{{ $dailyTotal }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Weekly</h5>
                        <input type="week" id="weekly-date-picker" class="form-control mb-3"
                            value="{{ \Carbon\Carbon::today()->format('Y-\WW') }}">
                        <p class="card-text" id="weekly-total">{{ $weeklyTotal }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Monthly</h5>
                        <input type="month" id="monthly-date-picker" class="form-control mb-3"
                            value="{{ \Carbon\Carbon::today()->format('Y-m') }}">
                        <p class="card-text" id="monthly-total">{{ $monthlyTotal }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to handle date change and fetch analytics data
        function fetchAnalyticsData(dateType, value) {
            let url = '/analytics/fetch-data?' + dateType + '=' + value;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // Update the relevant section with the new data
                    if (data.status === 1) {
                        // Update the totals based on the response
                        document.getElementById('daily-total').textContent = data.dailyTotal;
                        document.getElementById('weekly-total').textContent = data.weeklyTotal;
                        document.getElementById('monthly-total').textContent = data.monthlyTotal;
                    } else {
                        console.error('Error fetching analytics data:', data.payload);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Daily date picker
        const dailyDatePicker = document.getElementById('daily-date-picker');
        if (dailyDatePicker) {
            dailyDatePicker.addEventListener('change', function() {
                fetchAnalyticsData('date', dailyDatePicker.value);
            });
        }

        // Weekly date picker
        const weeklyDatePicker = document.getElementById('weekly-date-picker');
        if (weeklyDatePicker) {
            weeklyDatePicker.addEventListener('change', function() {
                fetchAnalyticsData('week', weeklyDatePicker.value);
            });
        }

        // Monthly date picker
        const monthlyDatePicker = document.getElementById('monthly-date-picker');
        if (monthlyDatePicker) {
            monthlyDatePicker.addEventListener('change', function() {
                fetchAnalyticsData('month', monthlyDatePicker.value);
            });
        }
    });
</script>
