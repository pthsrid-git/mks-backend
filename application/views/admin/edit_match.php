<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Match</h3>
    </div>
    <div class="card-body">
        <form action="<?php echo base_url('admin/edit-match/' . $match->id); ?>" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>League</label>
                        <select name="league_id" class="form-control" required>
                            <option value="">Select League</option>
                            <?php foreach ($leagues as $league): ?>
                            <option value="<?php echo $league->id; ?>" <?php echo $match->league_id == $league->id ? 'selected' : ''; ?>>
                                <?php echo $league->name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Season</label>
                        <input type="text" name="season" class="form-control" value="<?php echo $match->season; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Home Team</label>
                        <select name="home_team_id" class="form-control" required>
                            <option value="">Select Home Team</option>
                            <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team->id; ?>" <?php echo $match->home_team_id == $team->id ? 'selected' : ''; ?>>
                                <?php echo $team->name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Away Team</label>
                        <select name="away_team_id" class="form-control" required>
                            <option value="">Select Away Team</option>
                            <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team->id; ?>" <?php echo $match->away_team_id == $team->id ? 'selected' : ''; ?>>
                                <?php echo $team->name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Match Date</label>
                        <input type="date" name="match_date" class="form-control" value="<?php echo $match->match_date; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Match Time</label>
                        <input type="time" name="match_time" class="form-control" value="<?php echo $match->match_time; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Week Number</label>
                        <input type="number" name="week_number" class="form-control" value="<?php echo $match->week_number; ?>" min="1" max="52" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Home Score</label>
                        <input type="number" name="home_score" class="form-control" value="<?php echo $match->home_score; ?>" min="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Away Score</label>
                        <input type="number" name="away_score" class="form-control" value="<?php echo $match->away_score; ?>" min="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="scheduled" <?php echo $match->status == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="ongoing" <?php echo $match->status == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="finished" <?php echo $match->status == 'finished' ? 'selected' : ''; ?>>Finished</option>
                            <option value="cancelled" <?php echo $match->status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Match</button>
                <a href="<?php echo base_url('admin/matches'); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>