/**
 * Squash Trivia Page JavaScript
 * Handles data fetching, map rendering, and interactive features
 */

(function($) {
    'use strict';

    // Global trivia object
    window.squashTrivia = {
        maps: {},
        data: {},
        config: window.squashTriviaConfig || { apiUrl: 'https://stats.squashplayers.app/api' },

        /**
         * Initialize all trivia sections
         */
        init: function() {
            console.log('Initializing Squash Trivia...');
            console.log('API URL:', this.config.apiUrl);
            console.log('Config object:', this.config);
            
            // Load data for each section
            if ($('#countries-without-venues').length) {
                this.loadCountriesWithoutVenues();
            }
            
            if ($('#high-altitude').length) {
                this.loadHighAltitudeVenues();
            }
            
            if ($('#extreme-latitude').length) {
                this.loadExtremeLatitudeVenues();
            }
            
            if ($('#hotels-resorts').length) {
                this.loadHotelsAndResorts();
            }
            
            if ($('#population-area').length) {
                this.loadPopulationAreaStats();
            }
            
            if ($('#unknown-courts').length) {
                this.loadUnknownCourts();
            }
            
            if ($('#country-club').length) {
                this.loadCountryClub();
            }
            
            if ($('#word-cloud').length) {
                this.loadWordCloud();
            }
            
            if ($('#loneliest').length) {
                this.loadLoneliestCourts();
            }
            
            if ($('#graveyard').length) {
                this.loadGraveyard();
            }
        },

        /**
         * Fetch data from API
         */
        fetchAPI: function(endpoint) {
            return $.ajax({
                url: this.config.apiUrl + endpoint,
                method: 'GET',
                dataType: 'json',
                timeout: 30000,
                error: function(xhr, status, error) {
                    console.error('API Error:', endpoint, error);
                }
            });
        },

        /**
         * Load Countries Without Venues
         */
        loadCountriesWithoutVenues: function() {
            var self = this;
            
            this.fetchAPI('/countries-without-venues').done(function(response) {
                self.data.countriesWithout = response;
                
                // Update count
                $('#countries-without-count').text(response.countries.length);
                
                // Render map
                self.renderCountriesWithoutMap(response);
            });
        },

        /**
         * Render Countries Without Venues Map
         */
        renderCountriesWithoutMap: function(data) {
            var map = L.map('countries-without-map').setView([20, 0], 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(map);
            
            // Add markers for countries without venues (if coordinates available)
            // This would require country centroid data
            
            this.maps.countriesWithout = map;
        },

        /**
         * Show Countries Without List (modal or expanded view)
         */
        showCountriesWithoutList: function() {
            if (!this.data.countriesWithout) return;
            
            var countries = this.data.countriesWithout.countries;
            var html = '<div class="countries-list-modal"><h3>Countries Without Squash Venues</h3><ul>';
            
            countries.forEach(function(country) {
                html += '<li>' + country.name + '</li>';
            });
            
            html += '</ul><button onclick="squashTrivia.closeModal()">Close</button></div>';
            
            $('body').append('<div class="modal-overlay" onclick="squashTrivia.closeModal()">' + html + '</div>');
        },

        /**
         * Load High Altitude Venues
         */
        loadHighAltitudeVenues: function() {
            var self = this;
            
            this.fetchAPI('/venues-with-elevation').done(function(response) {
                self.data.highAltitude = response;
                
                // Filter venues above 2000m
                var highVenues = response.venues.filter(function(v) {
                    return v.elevation >= 2000;
                });
                
                // Render map
                self.renderHighAltitudeMap(highVenues);
                
                // Render table (top 10)
                self.renderHighAltitudeTable(highVenues.slice(0, 10));
            });
        },

        /**
         * Render High Altitude Map
         */
        renderHighAltitudeMap: function(venues) {
            var map = L.map('high-altitude-map').setView([0, 0], 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(map);
            
            var bounds = [];
            
            venues.forEach(function(venue) {
                if (venue.latitude && venue.longitude) {
                    var color = venue.elevation >= 3500 ? '#dc2626' : 
                               venue.elevation >= 3000 ? '#f59e0b' : '#10b981';
                    
                    var marker = L.circleMarker([venue.latitude, venue.longitude], {
                        radius: 6,
                        fillColor: color,
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).addTo(map);
                    
                    marker.bindPopup('<strong>' + venue.name + '</strong><br>' +
                                   venue.city + ', ' + venue.country + '<br>' +
                                   'Elevation: ' + venue.elevation + 'm');
                    
                    bounds.push([venue.latitude, venue.longitude]);
                }
            });
            
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
            
            this.maps.highAltitude = map;
        },

        /**
         * Render High Altitude Table
         */
        renderHighAltitudeTable: function(venues) {
            var tbody = $('#high-altitude-table tbody');
            tbody.empty();
            
            venues.forEach(function(venue, index) {
                var elevationClass = venue.elevation >= 3500 ? 'elevation-3500' :
                                    venue.elevation >= 3000 ? 'elevation-3000' : 'elevation-2000';
                
                tbody.append(
                    '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + venue.name + '</td>' +
                    '<td>' + (venue.city || '-') + '</td>' +
                    '<td>' + venue.country + '</td>' +
                    '<td><span class="elevation-badge ' + elevationClass + '">' + venue.elevation + 'm</span></td>' +
                    '</tr>'
                );
            });
        },

        /**
         * Load Extreme Latitude Venues
         */
        loadExtremeLatitudeVenues: function() {
            var self = this;
            
            this.fetchAPI('/extreme-latitude-venues').done(function(response) {
                self.data.extremeLatitude = response;
                
                // Render map (showing northerly by default)
                self.renderExtremeLatitudeMap(response.northerly);
                
                // Render tables
                self.renderExtremeLatitudeTable('northerly', response.northerly);
                self.renderExtremeLatitudeTable('southerly', response.southerly);
            });
        },

        /**
         * Switch between northerly/southerly tabs
         */
        switchTab: function(type) {
            // Update tab buttons
            $('.trivia-tabs .tab-btn').removeClass('active');
            $('.trivia-tabs .tab-btn').filter(function() {
                return $(this).text().toLowerCase().includes(type);
            }).addClass('active');
            
            // Update content
            $('.tab-content').removeClass('active');
            $('#' + type + '-table-container').addClass('active');
            
            // Update map
            if (this.data.extremeLatitude) {
                this.renderExtremeLatitudeMap(this.data.extremeLatitude[type]);
            }
        },

        /**
         * Render Extreme Latitude Map
         */
        renderExtremeLatitudeMap: function(venues) {
            // Clear existing map if present
            if (this.maps.extremeLatitude) {
                this.maps.extremeLatitude.remove();
            }
            
            var map = L.map('extreme-latitude-map').setView([0, 0], 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(map);
            
            var bounds = [];
            
            venues.forEach(function(venue) {
                if (venue.latitude && venue.longitude) {
                    var marker = L.circleMarker([venue.latitude, venue.longitude], {
                        radius: 6,
                        fillColor: '#667eea',
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).addTo(map);
                    
                    marker.bindPopup('<strong>' + venue.name + '</strong><br>' +
                                   venue.city + ', ' + venue.country + '<br>' +
                                   'Latitude: ' + venue.latitude.toFixed(4) + '°');
                    
                    bounds.push([venue.latitude, venue.longitude]);
                }
            });
            
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
            
            this.maps.extremeLatitude = map;
        },

        /**
         * Render Extreme Latitude Table
         */
        renderExtremeLatitudeTable: function(type, venues) {
            var tbody = $('#' + type + '-table tbody');
            tbody.empty();
            
            venues.slice(0, 20).forEach(function(venue, index) {
                tbody.append(
                    '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + venue.name + '</td>' +
                    '<td>' + (venue.city || '-') + '</td>' +
                    '<td>' + venue.country + '</td>' +
                    '<td><span class="latitude-badge">' + venue.latitude.toFixed(4) + '°</span></td>' +
                    '</tr>'
                );
            });
        },

        /**
         * Load Hotels and Resorts
         */
        loadHotelsAndResorts: function() {
            var self = this;
            
            this.fetchAPI('/hotels-and-resorts').done(function(response) {
                self.data.hotels = response;
                
                // Update count
                $('#hotels-count').text(response.venues.length);
                
                // Render map
                self.renderHotelsMap(response.venues);
            });
        },

        /**
         * Render Hotels Map
         */
        renderHotelsMap: function(venues) {
            var map = L.map('hotels-map').setView([20, 0], 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(map);
            
            var bounds = [];
            
            venues.forEach(function(venue) {
                if (venue.latitude && venue.longitude) {
                    var marker = L.circleMarker([venue.latitude, venue.longitude], {
                        radius: 6,
                        fillColor: '#10b981',
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).addTo(map);
                    
                    marker.bindPopup('<strong>' + venue.name + '</strong><br>' +
                                   venue.city + ', ' + venue.country + '<br>' +
                                   'Courts: ' + (venue.courts || '?'));
                    
                    bounds.push([venue.latitude, venue.longitude]);
                }
            });
            
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
            
            this.maps.hotels = map;
        },

        /**
         * Filter hotels by continent
         */
        filterHotels: function(continentId) {
            // Re-render map with filtered data
            // Implementation depends on data structure
            console.log('Filter hotels by continent:', continentId);
        },

        /**
         * Show Hotels List
         */
        showHotelsList: function() {
            // Show modal with full list
            console.log('Show hotels list');
        },

        /**
         * Load Population & Area Stats
         */
        loadPopulationAreaStats: function() {
            var self = this;
            
            this.fetchAPI('/countries-with-venues-stats').done(function(response) {
                self.data.populationArea = response;
                self.renderPopulationAreaTable(response.countries);
            });
        },

        /**
         * Render Population & Area Table
         */
        renderPopulationAreaTable: function(countries) {
            var tbody = $('#population-area-table tbody');
            tbody.empty();
            
            countries.forEach(function(country, index) {
                tbody.append(
                    '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + country.name + '</td>' +
                    '<td>' + (country.population / 1000000).toFixed(2) + '</td>' +
                    '<td>' + (country.area / 1000000).toFixed(2) + '</td>' +
                    '<td>' + country.venues + '</td>' +
                    '<td>' + country.courts + '</td>' +
                    '<td>' + country.venues_per_million.toFixed(2) + '</td>' +
                    '<td>' + country.courts_per_million.toFixed(2) + '</td>' +
                    '<td>' + country.venues_per_1000_sqkm.toFixed(2) + '</td>' +
                    '<td>' + country.courts_per_1000_sqkm.toFixed(2) + '</td>' +
                    '</tr>'
                );
            });
            
            // Make table sortable
            this.makeSortable($('#population-area-table'));
        },

        /**
         * Load Unknown Courts
         */
        loadUnknownCourts: function() {
            var self = this;
            
            this.fetchAPI('/venues-with-unknown-courts').done(function(response) {
                self.data.unknownCourts = response;
                
                // Update count
                $('#unknown-courts-count').text(response.venues.length);
                
                // Render map
                self.renderUnknownCourtsMap(response.venues);
            });
        },

        /**
         * Render Unknown Courts Map
         */
        renderUnknownCourtsMap: function(venues) {
            var map = L.map('unknown-courts-map').setView([20, 0], 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(map);
            
            var bounds = [];
            
            venues.forEach(function(venue) {
                if (venue.latitude && venue.longitude) {
                    var marker = L.circleMarker([venue.latitude, venue.longitude], {
                        radius: 5,
                        fillColor: '#f59e0b',
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.7
                    }).addTo(map);
                    
                    marker.bindPopup('<strong>' + venue.name + '</strong><br>' +
                                   venue.city + ', ' + venue.country);
                    
                    bounds.push([venue.latitude, venue.longitude]);
                }
            });
            
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
            
            this.maps.unknownCourts = map;
        },

        /**
         * Filter unknown courts by continent
         */
        filterUnknownCourts: function(continentId) {
            console.log('Filter unknown courts by continent:', continentId);
        },

        /**
         * Show Unknown Courts List
         */
        showUnknownCourtsList: function() {
            console.log('Show unknown courts list');
        },

        /**
         * Load Country Club 100%
         */
        loadCountryClub: function() {
            var self = this;
            
            this.fetchAPI('/country-club-100-percent').done(function(response) {
                self.data.countryClub = response;
                self.renderCountryClubTable(response.countries);
            });
        },

        /**
         * Render Country Club Table
         */
        renderCountryClubTable: function(countries) {
            var tbody = $('#country-club-table tbody');
            tbody.empty();
            
            var totalVenues = 0, totalKnown = 0, totalCourts = 0;
            
            countries.forEach(function(country, index) {
                totalVenues += country.total_venues;
                totalKnown += country.venues_with_courts;
                totalCourts += country.total_courts;
                
                tbody.append(
                    '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + country.name + '</td>' +
                    '<td>' + country.total_venues + '</td>' +
                    '<td>' + country.venues_with_courts + '</td>' +
                    '<td>' + country.total_courts + '</td>' +
                    '<td>' + country.percentage.toFixed(1) + '%</td>' +
                    '<td>' + country.courts_per_venue.toFixed(2) + '</td>' +
                    '</tr>'
                );
            });
            
            // Update footer summary
            $('#club-total-venues').text(totalVenues);
            $('#club-total-known').text(totalKnown);
            $('#club-total-courts').text(totalCourts);
            $('#club-avg-percentage').text(((totalKnown / totalVenues) * 100).toFixed(1) + '%');
            $('#club-avg-courts-per-venue').text((totalCourts / totalVenues).toFixed(2));
            
            // Make table sortable
            this.makeSortable($('#country-club-table'));
        },

        /**
         * Filter Country Club by continent
         */
        filterCountryClub: function(continentId) {
            console.log('Filter country club by continent:', continentId);
        },

        /**
         * Load Word Cloud
         */
        loadWordCloud: function() {
            var self = this;
            
            this.fetchAPI('/countries-wordcloud').done(function(response) {
                self.data.wordCloud = response;
                self.renderWordCloud(response.countries);
            });
        },

        /**
         * Render Word Cloud
         */
        renderWordCloud: function(countries) {
            var canvas = document.getElementById('word-cloud-canvas');
            if (!canvas || typeof WordCloud === 'undefined') return;
            
            // Prepare data for WordCloud2
            var list = countries.map(function(country) {
                var color = country.venues >= 500 ? '#1e40af' :
                           country.venues >= 100 ? '#3b82f6' :
                           country.venues >= 50 ? '#60a5fa' :
                           country.venues >= 10 ? '#93c5fd' : '#dbeafe';
                
                return [country.name, country.venues, color];
            });
            
            WordCloud(canvas, {
                list: list,
                gridSize: 8,
                weightFactor: function(size) {
                    return Math.pow(size, 0.5) * 5;
                },
                fontFamily: 'Arial, sans-serif',
                color: function(word, weight, fontSize, distance, theta) {
                    // Color is provided in the list
                    return word[2];
                },
                rotateRatio: 0.3,
                backgroundColor: '#f7fafc'
            });
        },

        /**
         * Load Loneliest Courts
         */
        loadLoneliestCourts: function() {
            var self = this;
            
            this.fetchAPI('/loneliest-courts').done(function(response) {
                self.data.loneliest = response;
                
                // Render map
                self.renderLoneliestMap(response.venues);
                
                // Render table (top 10)
                self.renderLoneliestTable(response.venues.slice(0, 10));
            });
        },

        /**
         * Render Loneliest Courts Map
         */
        renderLoneliestMap: function(venues) {
            var map = L.map('loneliest-map').setView([0, 0], 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(map);
            
            var bounds = [];
            
            venues.forEach(function(venue) {
                if (venue.latitude && venue.longitude) {
                    // Draw line to nearest neighbor
                    if (venue.nearest_latitude && venue.nearest_longitude) {
                        L.polyline([
                            [venue.latitude, venue.longitude],
                            [venue.nearest_latitude, venue.nearest_longitude]
                        ], {
                            color: '#f59e0b',
                            weight: 2,
                            opacity: 0.6,
                            dashArray: '5, 10'
                        }).addTo(map);
                    }
                    
                    // Add marker for venue
                    var marker = L.circleMarker([venue.latitude, venue.longitude], {
                        radius: 7,
                        fillColor: '#dc2626',
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).addTo(map);
                    
                    marker.bindPopup('<strong>' + venue.name + '</strong><br>' +
                                   venue.city + ', ' + venue.country + '<br>' +
                                   'Distance to nearest: ' + venue.distance_km.toFixed(1) + ' km');
                    
                    bounds.push([venue.latitude, venue.longitude]);
                }
            });
            
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
            
            this.maps.loneliest = map;
        },

        /**
         * Render Loneliest Courts Table
         */
        renderLoneliestTable: function(venues) {
            var tbody = $('#loneliest-table tbody');
            tbody.empty();
            
            venues.forEach(function(venue, index) {
                tbody.append(
                    '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + venue.name + '</td>' +
                    '<td>' + (venue.city || '-') + '</td>' +
                    '<td>' + venue.country + '</td>' +
                    '<td><span class="distance-badge">' + venue.distance_km.toFixed(1) + ' km</span></td>' +
                    '</tr>'
                );
            });
        },

        /**
         * Load Graveyard
         */
        loadGraveyard: function() {
            var self = this;
            
            // Load graveyard data
            this.fetchAPI('/court-graveyard').done(function(response) {
                self.data.graveyard = response;
                
                // Update stats
                $('#graveyard-total-venues').text(response.total_venues);
                $('#graveyard-countries').text(response.countries_count);
                $('#graveyard-courts-lost').text(response.courts_lost);
                $('#graveyard-showing').text(response.venues.length);
                
                // Populate filter dropdowns
                self.populateGraveyardFilters(response);
                
                // Render table
                self.renderGraveyardTable(response.venues);
            });
            
            // Load deletion reasons
            this.fetchAPI('/deletion-reasons').done(function(response) {
                self.data.deletionReasons = response;
            });
        },

        /**
         * Populate Graveyard Filters
         */
        populateGraveyardFilters: function(data) {
            // Populate country filter
            var countrySelect = $('#graveyard-country-filter');
            var countries = [...new Set(data.venues.map(v => v.country))].sort();
            
            countries.forEach(function(country) {
                countrySelect.append('<option value="' + country + '">' + country + '</option>');
            });
            
            // Populate reason filter (if deletion reasons loaded)
            if (this.data.deletionReasons) {
                var reasonSelect = $('#graveyard-reason-filter');
                this.data.deletionReasons.forEach(function(reason) {
                    reasonSelect.append('<option value="' + reason.id + '">' + reason.name + '</option>');
                });
            }
        },

        /**
         * Render Graveyard Table
         */
        renderGraveyardTable: function(venues) {
            var tbody = $('#graveyard-table tbody');
            tbody.empty();
            
            if (venues.length === 0) {
                tbody.append('<tr><td colspan="6">No venues found</td></tr>');
                return;
            }
            
            venues.forEach(function(venue) {
                var deathClass = 'death-other';
                if (venue.delete_reason_name && venue.delete_reason_name.toLowerCase().includes('closed')) {
                    deathClass = 'death-closed';
                } else if (venue.delete_reason_name && venue.delete_reason_name.toLowerCase().includes('duplicate')) {
                    deathClass = 'death-duplicate';
                } else if (venue.delete_reason_name && venue.delete_reason_name.toLowerCase().includes('never')) {
                    deathClass = 'death-never-existed';
                }
                
                tbody.append(
                    '<tr>' +
                    '<td>' + venue.name + '</td>' +
                    '<td>' + (venue.address || '-') + '</td>' +
                    '<td>' + venue.country + '</td>' +
                    '<td>' + (venue.courts || 0) + '</td>' +
                    '<td><span class="death-badge ' + deathClass + '">' + (venue.delete_reason_name || 'Unknown') + '</span></td>' +
                    '<td>' + (venue.deleted_at || '-') + '</td>' +
                    '</tr>'
                );
            });
        },

        /**
         * Filter Graveyard
         */
        filterGraveyard: function(filterType, value) {
            var params = {};
            
            if (filterType === 'country' && value) {
                params.country = value;
            } else if (filterType === 'reason' && value) {
                params.delete_reason_id = value;
            }
            
            var self = this;
            var queryString = $.param(params);
            
            this.fetchAPI('/court-graveyard' + (queryString ? '?' + queryString : '')).done(function(response) {
                self.data.graveyard = response;
                
                // Update stats
                $('#graveyard-showing').text(response.venues.length);
                
                // Re-render table
                self.renderGraveyardTable(response.venues);
            });
        },

        /**
         * Make table sortable
         */
        makeSortable: function($table) {
            var self = this;
            
            $table.find('thead th').click(function() {
                var $th = $(this);
                var columnIndex = $th.index();
                var $tbody = $table.find('tbody');
                var rows = $tbody.find('tr').toArray();
                
                var isAscending = $th.hasClass('sorted-asc');
                
                // Remove all sorting classes
                $table.find('th').removeClass('sorted-asc sorted-desc');
                
                // Sort rows
                rows.sort(function(a, b) {
                    var aValue = $(a).find('td').eq(columnIndex).text();
                    var bValue = $(b).find('td').eq(columnIndex).text();
                    
                    // Try to parse as number
                    var aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
                    var bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));
                    
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return isAscending ? bNum - aNum : aNum - bNum;
                    } else {
                        if (isAscending) {
                            return bValue.localeCompare(aValue);
                        } else {
                            return aValue.localeCompare(bValue);
                        }
                    }
                });
                
                // Update table
                $tbody.empty();
                $.each(rows, function(index, row) {
                    $tbody.append(row);
                });
                
                // Update sort indicator
                $th.addClass(isAscending ? 'sorted-desc' : 'sorted-asc');
            });
        },

        /**
         * Close modal
         */
        closeModal: function() {
            $('.modal-overlay').remove();
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.squash-trivia-wrapper').length) {
            squashTrivia.init();
        }
    });

})(jQuery);

