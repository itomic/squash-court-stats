<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">
            <i class="fas fa-map-marker-alt me-2"></i>Loneliest Squash Courts
        </h5>
        
        <p class="text-muted">
            These are the squash venues that are furthest from their nearest neighbor. 
            Lines connect each venue to its closest squash court.
        </p>
        
        <!-- Legend -->
        <div class="mb-3 d-flex align-items-center gap-3 flex-wrap">
            <div class="small text-muted">
                <span style="color: #dc2626;">●</span> Loneliest venue
                <span style="color: #3b82f6;">●</span> Nearest neighbor
                <span style="color: #9ca3af;">━</span> Distance
            </div>
            <div class="ms-auto">
                <span class="badge bg-primary" id="loneliest-venues-count">Loading...</span>
            </div>
        </div>
        
        <!-- Map Container -->
        <div id="loneliest-courts-map" style="height: 600px; border-radius: 0.5rem;"></div>
        
        <!-- Top 10 List (collapsible) -->
        <div class="mt-3">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#loneliestVenuesList" aria-expanded="false">
                <i class="fas fa-list me-1"></i>View Top 10 Loneliest Venues
            </button>
            <div class="collapse mt-2" id="loneliestVenuesList">
                <div class="card card-body">
                    <div id="loneliest-venues-list-content">
                        <div class="text-center text-muted">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            Loading...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

