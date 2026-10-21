<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Property Categories, Types & Amenities</h1>
        <p class="dashboard-subtitle">Configure real estate sectors, property classification taxonomy, and asset feature tags.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start;">
    <!-- Categories List -->
    <div class="table-card">
        <div class="table-header">
            <h3 style="font-size: 18px; font-weight: 600;">Property Categories</h3>
            <span class="badge badge-primary"><?= count($categories) ?> Categories</span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Properties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td style="font-size: 20px;"><?= e($cat['icon'] ?? '🏢') ?></td>
                        <td><strong><?= e($cat['name']) ?></strong></td>
                        <td><code><?= e($cat['slug']) ?></code></td>
                        <td>
                            <span class="badge badge-gold">
                                <?= $cat['property_count'] ?? 0 ?> Active
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add Category Form -->
        <div style="padding: 20px; border-top: 1px solid var(--color-border); background: #F8FAFC;">
            <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px;">Add New Category</h4>
            <form action="<?= url('admin/categories/store') ?>" method="POST" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px;">
                <?= csrf_field() ?>
                <input type="text" name="name" class="form-control" placeholder="Category Name" required>
                <input type="text" name="icon" class="form-control" placeholder="Icon (e.g. 🏡)" style="width: 100px;">
                <button type="submit" class="btn btn-primary btn-sm">Create</button>
            </form>
        </div>
    </div>

    <!-- Amenities List -->
    <div class="table-card">
        <div class="table-header">
            <h3 style="font-size: 18px; font-weight: 600;">Lifestyle Amenities & Features</h3>
            <span class="badge badge-primary"><?= count($amenities) ?> Amenities</span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Group</th>
                    <th>Amenity Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($amenities as $am): ?>
                    <tr>
                        <td><span class="badge badge-primary"><?= strtoupper($am['group_name'] ?? 'General') ?></span></td>
                        <td><strong><?= e($am['name']) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add Amenity Form -->
        <div style="padding: 20px; border-top: 1px solid var(--color-border); background: #F8FAFC;">
            <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px;">Add New Amenity</h4>
            <form action="<?= url('admin/amenities/store') ?>" method="POST" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px;">
                <?= csrf_field() ?>
                <input type="text" name="name" class="form-control" placeholder="Amenity Name" required>
                <select name="group_name" class="form-control">
                    <option value="security">Security & Infrastructure</option>
                    <option value="leisure">Leisure & Wellness</option>
                    <option value="interior">Interior Appointments</option>
                    <option value="eco">Eco & Energy</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Create</button>
            </form>
        </div>
    </div>
</div>
