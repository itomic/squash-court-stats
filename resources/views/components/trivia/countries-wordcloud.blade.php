<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">
            <i class="fas fa-cloud me-2"></i>Countries by # of Squash Venues
        </h5>
        
                <p class="text-muted mb-3">
                    The size of each country name represents the number of squash venues. Larger text = more venues!
                </p>
                
                <!-- Color Legend -->
                <div class="mb-3 d-flex flex-wrap gap-3 align-items-center">
                    <small class="fw-bold">Color bands:</small>
                    <div id="countries-wordcloud-legend" class="d-flex flex-wrap gap-2">
                        <!-- Legend will be populated by JavaScript based on actual venue counts -->
                        <span class="badge" style="background-color: #dc3545;">Loading...</span>
                    </div>
                </div>
        
        <!-- Loading Indicator -->
        <div id="wordcloud-loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading word cloud...</p>
        </div>
        
                <!-- Chart Container -->
                <div id="wordcloud-container" class="d-none" style="position: relative;">
                    <canvas id="countries-wordcloud-canvas" width="1200" height="800" style="width: 100%; height: auto;"></canvas>
                    <div id="wordcloud-tooltip" style="position: absolute; display: none; background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; border-radius: 4px; font-size: 14px; pointer-events: none; z-index: 1000;"></div>
                </div>
    </div>
</div>

