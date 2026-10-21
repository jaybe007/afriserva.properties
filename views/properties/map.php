<div style="background: #FFFFFF; border-bottom: 1px solid var(--color-border); padding: 16px 24px;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 22px; margin-bottom: 4px;">Interactive Property Map Discovery</h1>
            <p style="font-size: 13px; color: var(--color-text-muted);">
                Explore luxury properties and verified land parcels visually across Nigeria.
            </p>
        </div>

        <div style="display: flex; gap: 12px; align-items: center;">
            <select id="map-filter-type" class="form-control" style="width: 150px; height: 38px;">
                <option value="">All Types</option>
                <option value="sale">For Sale</option>
                <option value="rent">For Rent</option>
                <option value="short_let">Short Let</option>
                <option value="land">Land</option>
            </select>

            <select id="map-filter-state" class="form-control" style="width: 150px; height: 38px;">
                <option value="">All States</option>
                <?php foreach ($states as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <a href="<?= url('properties') ?>" class="btn btn-outline btn-sm">
                Switch to Grid View ⊞
            </a>
        </div>
    </div>
</div>

<div class="container" style="padding: 24px 0 60px;">
    <div id="map-container" style="height: 680px; width: 100%; border-radius: var(--radius-xl); box-shadow: var(--shadow-luxury); border: 1px solid var(--color-border);"></div>
</div>

<script type="module">
import { initPropertyMap } from "<?= asset('js/modules/map.js') ?>";

document.addEventListener('DOMContentLoaded', () => {
    const mapController = initPropertyMap('map-container', '<?= url('api/properties/coordinates') ?>');

    const typeSelect = document.getElementById('map-filter-type');
    const stateSelect = document.getElementById('map-filter-state');

    function applyMapFilters() {
        const params = new URLSearchParams();
        if (typeSelect.value) params.append('type', typeSelect.value);
        if (stateSelect.value) params.append('state', stateSelect.value);
        mapController.reload(params.toString());
    }

    typeSelect?.addEventListener('change', applyMapFilters);
    stateSelect?.addEventListener('change', applyMapFilters);
});
</script>
