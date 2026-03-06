<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Match Score Updates</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="updateAllScores()">
                            <i class="fas fa-sync"></i> Update All Scores
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- System Status -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="far fa-futbol"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Recent Matches</span>
                                    <span class="info-box-number" id="total-matches">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-play-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Ongoing</span>
                                    <span class="info-box-number" id="ongoing-matches">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-flag-checkered"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Finished</span>
                                    <span class="info-box-number" id="finished-matches">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-secondary">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Next Update</span>
                                    <span class="info-box-number" id="next-update">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- API Configuration -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Football-Data.org API Configuration</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="api_key">API Key</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="api_key" 
                                                   placeholder="Enter your Football-Data.org API key">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" onclick="saveApiKey()">
                                                    <i class="fas fa-save"></i> Save
                                                </button>
                                                <button type="button" class="btn btn-info" onclick="testApiConnection()">
                                                    <i class="fas fa-plug"></i> Test
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            Get your free API key from: <a href="https://www.football-data.org/client/register" target="_blank">https://www.football-data.org</a>
                                        </small>
                                    </div>
                                    
                                    <!-- API Status -->
                                    <div id="api-status">
                                        <div class="alert alert-<?= $api_configured ? 'success' : 'warning' ?>">
                                            <i class="fas fa-<?= $api_configured ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                                            <?php if ($api_configured): ?>
                                                API Key is configured (<?= $api_key ?>)
                                            <?php else: ?>
                                                API Key not configured - Using mock data for development
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Auto Update Settings -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">Auto Update Settings</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="auto-update-switch" <?= ($auto_update_enabled == 1) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="auto-update-switch">Enable Auto Score Updates</label>
                                        </div>
                                        <small class="form-text text-muted">
                                            When enabled, scores will be automatically updated every 5 minutes via cron job.
                                        </small>
                                    </div>
                                    
                                    <!-- Current Status Info -->
                                    <div class="alert alert-info">
                                        <strong>Current Status:</strong> 
                                        <?= ($auto_update_enabled == 1) ? 
                                            '<span class="text-success">Auto Update ENABLED</span>' : 
                                            '<span class="text-danger">Auto Update DISABLED</span>' ?>
                                        <br>
                                        <small>
                                            Cron Command: <code>php <?= FCPATH ?>index.php cron update_scores</code>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Matches Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Recent Matches (Last 2 Days)</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" onclick="loadMatchesTable()" title="Refresh">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Match</th>
                                                <th>Date & Time</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                                <th>Last Update</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="matches-table-body">
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                    <p>Loading matches...</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Logs -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Recent Update Logs</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" onclick="loadUpdateLogs()" title="Refresh">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="update-logs">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <p>Loading logs...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manual Update Modal -->
<div class="modal fade" id="manualUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manual Score Update</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="manual-update-result"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Load initial data
$(document).ready(function() {
    loadSystemStatus();
    loadMatchesTable();
    loadUpdateLogs();
    
    // Auto refresh every 30 seconds
    setInterval(loadSystemStatus, 30000);
    setInterval(loadMatchesTable, 30000);
    setInterval(loadUpdateLogs, 30000);
});

// Load system status
function loadSystemStatus() {
    $.ajax({
        url: '<?= site_url('admin/match_update/get_status') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#total-matches').text(response.data.total_recent_matches);
                $('#ongoing-matches').text(response.data.ongoing_matches);
                $('#finished-matches').text(response.data.finished_matches);
                $('#next-update').text(formatDateTime(response.data.next_update_available));
            }
        },
        error: function() {
            console.error('Failed to load system status');
        }
    });
}

// Load matches table
function loadMatchesTable() {
    $.ajax({
        url: '<?= site_url('admin/match_update/get_recent_matches') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let html = '';
                if (response.data.matches.length === 0) {
                    html = '<tr><td colspan="7" class="text-center text-muted">No recent matches found</td></tr>';
                } else {
                    response.data.matches.forEach(function(match) {
                        html += `
                            <tr>
                                <td>${match.id}</td>
                                <td>
                                    <strong>${match.home_team}</strong> vs <strong>${match.away_team}</strong>
                                    ${match.external_id ? `<br><small class="text-muted">External ID: ${match.external_id}</small>` : ''}
                                </td>
                                <td>${match.match_date} ${match.match_time}</td>
                                <td>
                                    <span class="badge badge-${getStatusBadge(match.status)}">
                                        ${match.status}
                                    </span>
                                </td>
                                <td>
                                    ${match.home_score !== null && match.away_score !== null ? 
                                        `<strong>${match.home_score} - ${match.away_score}</strong>` : 
                                        '<span class="text-muted">Not started</span>'
                                    }
                                </td>
                                <td>
                                    ${match.last_score_update ? 
                                        `<small>${formatDateTime(match.last_score_update)}</small>` : 
                                        '<span class="text-muted">Never</span>'
                                    }
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="updateSingleMatch(${match.id})" title="Update Scores">
                                            <i class="fas fa-sync"></i> Update
                                        </button>
                                        ${match.status === 'finished' ? 
                                            `<button class="btn btn-sm btn-outline-success" onclick="evaluatePredictions(${match.id})" title="Evaluate Predictions">
                                                <i class="fas fa-calculator"></i> Evaluate
                                            </button>` : ''
                                        }
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#matches-table-body').html(html);
            }
        },
        error: function() {
            $('#matches-table-body').html('<tr><td colspan="7" class="text-center text-danger">Failed to load matches</td></tr>');
        }
    });
}

// Load update logs
function loadUpdateLogs() {
    $.ajax({
        url: '<?= site_url('admin/match_update/get_update_logs') ?>',
        type: 'GET',
        success: function(response) {
            $('#update-logs').html(response);
        },
        error: function() {
            $('#update-logs').html('<p class="text-danger">Failed to load logs</p>');
        }
    });
}

// Update all scores
function updateAllScores() {
    if (!confirm('Are you sure you want to update all match scores? This may take a few moments.')) {
        return;
    }

    $('#manual-update-result').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Updating...</span>
            </div>
            <p>Updating all match scores...</p>
            <p><small>This may take a few seconds</small></p>
        </div>
    `);
    $('#manualUpdateModal').modal('show');

    $.ajax({
        url: '<?= site_url('admin/match_update/update_all') ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            let resultHtml = '';
            if (response.status === 'success') {
                const data = response.data;
                resultHtml = `
                    <div class="alert alert-success">
                        <h5><i class="icon fas fa-check"></i> Update Completed!</h5>
                        <p><strong>Updated:</strong> ${data.updated} matches</p>
                        <p><strong>Total checked:</strong> ${data.total_checked}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                `;
                
                if (data.errors && data.errors.length > 0) {
                    resultHtml += `
                        <p><strong>Errors:</strong> ${data.errors.length}</p>
                        <div class="border rounded p-2 bg-light">
                            <ul class="mb-0">
                    `;
                    data.errors.forEach(error => {
                        resultHtml += `<li class="text-danger small">${error}</li>`;
                    });
                    resultHtml += `
                            </ul>
                        </div>
                    `;
                } else {
                    resultHtml += `<p class="text-success"><strong>No errors encountered</strong></p>`;
                }
                
                resultHtml += `</div>`;
            } else {
                resultHtml = `
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> Update Failed!</h5>
                        <p>${response.message}</p>
                    </div>
                `;
            }
            $('#manual-update-result').html(resultHtml);
            loadSystemStatus();
            loadMatchesTable();
            loadUpdateLogs();
        },
        error: function(xhr, status, error) {
            $('#manual-update-result').html(`
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Request Failed!</h5>
                    <p>Unable to connect to server. Please try again.</p>
                    <p><small>Error: ${error}</small></p>
                </div>
            `);
        }
    });
}

// Update single match
function updateSingleMatch(matchId) {
    $('#manual-update-result').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Updating...</span>
            </div>
            <p>Updating match scores...</p>
        </div>
    `);
    $('#manualUpdateModal').modal('show');

    $.ajax({
        url: '<?= site_url('admin/match_update/update_single/') ?>' + matchId,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            let resultHtml = '';
            if (response.status === 'success') {
                resultHtml = `
                    <div class="alert alert-success">
                        <h5><i class="icon fas fa-check"></i> Update Successful!</h5>
                        <p>${response.message}</p>
                        ${response.score_changed ? 
                            `<p><strong>New Score:</strong> ${response.new_score}</p>
                             <p><strong>Status:</strong> ${response.status || 'updated'}</p>` : 
                            '<p>No changes detected - scores are already up to date</p>'
                        }
                    </div>
                `;
            } else {
                resultHtml = `
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> Update Failed!</h5>
                        <p>${response.message}</p>
                    </div>
                `;
            }
            $('#manual-update-result').html(resultHtml);
            loadMatchesTable();
            loadUpdateLogs();
        },
        error: function(xhr, status, error) {
            $('#manual-update-result').html(`
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Request Failed!</h5>
                    <p>Unable to connect to server. Please try again.</p>
                    <p><small>Error: ${error}</small></p>
                </div>
            `);
        }
    });
}

// Evaluate predictions
function evaluatePredictions(matchId) {
    if (!confirm('Evaluate predictions for this match? This will calculate points for all users who predicted this match.')) {
        return;
    }

    $('#manual-update-result').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Evaluating...</span>
            </div>
            <p>Evaluating predictions...</p>
            <p><small>Calculating points for users...</small></p>
        </div>
    `);
    $('#manualUpdateModal').modal('show');

    $.ajax({
        url: '<?= site_url('admin/match_update/evaluate_predictions/') ?>' + matchId,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            let resultHtml = '';
            if (response.status === 'success') {
                resultHtml = `
                    <div class="alert alert-success">
                        <h5><i class="icon fas fa-check"></i> Evaluation Complete!</h5>
                        <p>Successfully evaluated <strong>${response.data.evaluated_count}</strong> predictions</p>
                        <p>Points have been distributed to users based on their predictions</p>
                        <p class="mb-0"><small>Exact scores: 3 points, Correct outcome: 1 point</small></p>
                    </div>
                `;
            } else {
                resultHtml = `
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Evaluation Skipped</h5>
                        <p>${response.message}</p>
                        <p class="mb-0"><small>Make sure the match is finished and has valid scores</small></p>
                    </div>
                `;
            }
            $('#manual-update-result').html(resultHtml);
        },
        error: function(xhr, status, error) {
            $('#manual-update-result').html(`
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Request Failed!</h5>
                    <p>Unable to connect to server. Please try again.</p>
                    <p><small>Error: ${error}</small></p>
                </div>
            `);
        }
    });
}

// Save API Key
function saveApiKey() {
    const apiKey = $('#api_key').val().trim();
    
    if (!apiKey) {
        alert('Please enter API key');
        return;
    }
    
    $.ajax({
        url: '<?= site_url('match_update/save_api_key') ?>',
        type: 'POST',
        data: { api_key: apiKey },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showToast('success', 'API Key saved successfully');
                // Clear the input field
                $('#api_key').val('');
                // Update API status
                loadApiStatus();
            } else {
                showToast('error', 'Failed to save API Key: ' + response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to save API Key - Server error');
        }
    });
}

// Test API Connection
function testApiConnection() {
    $('#manual-update-result').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Testing...</span>
            </div>
            <p>Testing API connection to Football-Data.org...</p>
        </div>
    `);
    $('#manualUpdateModal').modal('show');

    $.ajax({
        url: '<?= site_url('match_update/test_api') ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            let resultHtml = '';
            const alertType = response.status === 'success' ? 'success' : 
                             response.status === 'warning' ? 'warning' : 'danger';
            
            resultHtml = `
                <div class="alert alert-${alertType}">
                    <h5><i class="icon fas fa-${alertType === 'success' ? 'check' : 'exclamation-triangle'}"></i> 
                    API Test ${response.status === 'success' ? 'Successful' : 'Failed'}</h5>
                    <p>${response.message}</p>
            `;
            
            if (response.status === 'success') {
                resultHtml += `
                    <p class="mb-0"><small>Your API key is working correctly with Football-Data.org</small></p>
                `;
            } else if (response.status === 'warning') {
                resultHtml += `
                    <p class="mb-0"><small>Rate limit exceeded - Free tier allows 10 requests per minute</small></p>
                `;
            } else {
                resultHtml += `
                    <p class="mb-0"><small>Please check your API key and try again</small></p>
                `;
            }
            
            resultHtml += `</div>`;
            $('#manual-update-result').html(resultHtml);
            
            // Update API status display
            loadApiStatus();
        },
        error: function(xhr, status, error) {
            $('#manual-update-result').html(`
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Test Failed!</h5>
                    <p>Unable to connect to server. Please try again.</p>
                    <p><small>Error: ${error}</small></p>
                </div>
            `);
        }
    });
}

// Load API status
function loadApiStatus() {
    $.ajax({
        url: '<?= site_url('match_update/get_api_status') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const data = response.data;
                let statusHtml = '';
                
                if (data.configured) {
                    statusHtml = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            API Key is configured (${data.key_preview})
                        </div>
                    `;
                } else {
                    statusHtml = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            API Key not configured - Using mock data for development
                        </div>
                    `;
                }
                
                $('#api-status').html(statusHtml);
            }
        }
    });
}

// Toggle auto update setting
$('#auto-update-switch').change(function() {
    const isEnabled = $(this).is(':checked') ? 1 : 0;
    
    $.ajax({
        url: '<?= site_url('match_update/toggle_auto_update') ?>',
        type: 'POST',
        data: { enabled: isEnabled },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showToast('success', 'Auto update settings updated successfully');
                // Update status display
                const statusElement = $('#auto-update-switch').closest('.card-body').find('.alert strong span');
                if (isEnabled) {
                    statusElement.removeClass('text-danger').addClass('text-success').text('Auto Update ENABLED');
                } else {
                    statusElement.removeClass('text-success').addClass('text-danger').text('Auto Update DISABLED');
                }
            } else {
                showToast('error', 'Failed to update settings');
                // Revert switch
                $('#auto-update-switch').prop('checked', !isEnabled);
            }
        },
        error: function() {
            showToast('error', 'Failed to update settings');
            $('#auto-update-switch').prop('checked', !isEnabled);
        }
    });
});

// Helper functions
function getStatusBadge(status) {
    const badges = {
        'scheduled': 'secondary',
        'ongoing': 'warning',
        'finished': 'success',
        'postponed': 'info',
        'cancelled': 'danger'
    };
    return badges[status] || 'secondary';
}

function formatDateTime(datetimeString) {
    if (!datetimeString) return '-';
    const date = new Date(datetimeString);
    return date.toLocaleString();
}

function showToast(type, message) {
    // Simple toast notification
    const toast = $(`<div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed bottom-0 end-0 m-3" role="alert">
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>`);
    
    $('body').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();
    
    // Remove after hide
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>