<div>
    <div class="d-flex gap-2 mb-3">
        <x-title label="Rental Management" icon="ticket-detailed" />
    </div>
    <section class="d-grid xl-grid-cols-4 lg-grid-cols-3 md-grid-cols-2 gap-3">
        <a href="{{ route('rentalInfo') }}" class="count-box">
            <div class="card-body">
                <h5 class="card-title">Rental List</h5>
                <span class="count-icon">
                    <i class="bi bi-house"></i>
                </span>
                <p class="card-text">Click to Proceed</p>
            </div>
        </a>
        <a href="{{ route('rentalReport') }}" class="count-box">
            <div class="card-body">
                <h5 class="card-title">Rental Reports</h5>
                <span class= "count-icon">
                    <i class="bi bi-file-text"></i>
                </span>
                <p class="card-text">Click to Proceed</p>
            </div>
        </a>
        <a href="{{ route('rentalCalculation') }}" class="count-box">
            <div class="card-body">
                <h5 class="card-title">Rental Calculation</h5>
                <span class= "count-icon">
                    <i class="bi bi-calculator"></i>
                </span>
                <p class="card-text">Click to Proceed</p>
            </div>
        </a>
        <a href="{{ route('download.summary.report') }}" class="count-box">
            <div class="card-body">
                <h5 class="card-title">Export Summary Report</h5>
                <span class= "count-icon">
                    <i class="bi bi-box-arrow-up-right"></i>
                </span>
                <p class="card-text">Click to Proceed</p>
            </div>
        </a>
        <a href="{{ route('rangeReport') }}" class="count-box">
            <div class="card-body">
                <h5 class="card-title">Range Reports</h5>
                <span class= "count-icon">
                    <i class="bi bi-file-text"></i>
                </span>
                <p class="card-text">Click to Proceed</p>
            </div>
        </a>
        <a href="{{ route('billingDashboard') }}" class="count-box">
            <div class="card-body">
                <h5 class="card-title">New Electricity Bills</h5>
                <span class= "count-icon">
                    <i class="bi bi-lightning-charge"></i>
                </span>
                <p class="card-text">Click to Proceed</p>
            </div>
        </a>
    </section>
    <style>
        .card-clickable {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
            color: inherit;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s, background-color 0.2s;
        }

        .card-clickable:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #e9ecef;
        }

        .card-body {
            padding: 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .card-footer {
            background-color: #f1f1f1;
            padding: 0.5rem;
            border-top: 1px solid #ddd;
            font-size: 0.9rem;
        }
    </style>
</div>
