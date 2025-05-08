<div class="col-md-4 mb-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-{{ $icon }} fa-2x text-{{ $color }}"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">{{ $label }}</h6>
                    <h4 class="mb-0">₹{{ number_format($value, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
