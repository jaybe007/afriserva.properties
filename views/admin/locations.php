<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 24px;">Geographic Location Management (36 States + FCT, LGAs & Global)</h2>
        <p style="color: var(--color-text-muted);">Configure active countries, states, municipal cities, local government areas (LGAs), and neighbourhoods.</p>
    </div>
</div>

<!-- Add New Geographic Entity Form -->
<div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 24px; margin-bottom: 32px; box-shadow: var(--shadow-subtle);">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;">Create Geographic Entity (State, City, LGA, Neighbourhood)</h3>
    <form action="<?= url('admin/locations/store') ?>" method="POST" style="display: grid; grid-template-columns: 180px 180px 1fr 1fr auto; gap: 16px; align-items: end;">
        <?= csrf_field() ?>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Location Type</label>
            <select name="location_type" id="location-type-select" class="form-control" required onchange="handleLocationTypeChange(this.value)">
                <option value="state">State / Territory</option>
                <option value="city">Municipal City</option>
                <option value="lga">Local Government (LGA)</option>
                <option value="neighbourhood">Neighbourhood</option>
            </select>
        </div>

        <div class="form-group" id="parent-state-group" style="margin-bottom: 0;">
            <label class="form-label" id="parent-label">State Jurisdiction</label>
            <select name="state_id" class="form-control">
                <?php foreach ($states as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['country_name']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Entity Name</label>
            <input type="text" name="name" class="form-control" required placeholder="e.g. Eti-Osa, Lekki, Ikeja, Maitama">
        </div>

        <div class="form-group" id="code-group" style="margin-bottom: 0;">
            <label class="form-label">Code / Abbr</label>
            <input type="text" name="code" class="form-control" placeholder="e.g. LA, FC">
        </div>

        <div>
            <button type="submit" class="btn btn-accent" style="height: 46px;">
                Save Entity
            </button>
        </div>
    </form>
</div>

<!-- States Table -->
<div class="table-card" style="margin-bottom: 32px;">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Indexed States & Regional Jurisdictions (<?= count($states) ?> Active)</h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Country</th>
                <th>State / Territory</th>
                <th>Slug</th>
                <th>ISO Code</th>
                <th>LGAs Indexed</th>
                <th>Cities</th>
                <th>Listings</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($states as $s): ?>
                <tr>
                    <td><strong><?= e($s['country_name']) ?></strong></td>
                    <td><span style="font-weight: 600; color: var(--color-primary);"><?= e($s['name']) ?></span></td>
                    <td><code><?= e($s['slug']) ?></code></td>
                    <td><code><?= e($s['code'] ?? '—') ?></code></td>
                    <td><span class="badge badge-primary"><?= $s['lgas_count'] ?? 0 ?> LGAs</span></td>
                    <td><?= $s['cities_count'] ?> cities</td>
                    <td>
                        <a href="<?= url('properties?state=' . $s['id']) ?>" target="_blank" style="color: var(--color-accent); font-weight: 600;">
                            <?= $s['properties_count'] ?> properties ↗
                        </a>
                    </td>
                    <td>
                        <span class="badge badge-verified">Active</span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Local Governments (LGAs) Table -->
<?php if (!empty($lgas)): ?>
<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Indexed Local Government Areas (<?= count($lgas) ?> LGAs)</h3>
    </div>
    <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px;">
        <?php foreach ($lgas as $lga): ?>
            <div style="background: var(--color-bg); padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--color-border); font-size: 13px;">
                <span style="font-weight: 600; color: var(--color-primary);"><?= e($lga['name']) ?></span><br>
                <small style="color: var(--color-text-muted);"><?= e($lga['state_name']) ?> State</small>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
function handleLocationTypeChange(type) {
    const codeGroup = document.getElementById('code-group');
    if (type === 'state') {
        codeGroup.style.display = 'block';
    } else {
        codeGroup.style.display = 'none';
    }
}
</script>
