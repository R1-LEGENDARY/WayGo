<label for="start_date">Pickup Date (optional)</label>
<input type="date" id="start_date" name="start_date" value="<?= isset($_GET['start_date']) ? e($_GET['start_date']) : '' ?>">

<label for="end_date">Return Date (optional)</label>
<input type="date" id="end_date" name="end_date" value="<?= isset($_GET['end_date']) ? e($_GET['end_date']) : '' ?>">

<label for="category">Category</label>
<select id="category" name="category">
    <option value="">Any</option>
    <option value="sedan" <?= $filters['category'] === 'sedan' ? 'selected' : '' ?>>Sedan</option>
    <option value="suv" <?= $filters['category'] === 'suv' ? 'selected' : '' ?>>SUV</option>
    <option value="van" <?= $filters['category'] === 'van' ? 'selected' : '' ?>>Van</option>
    <option value="pickup_truck" <?= $filters['category'] === 'pickup_truck' ? 'selected' : '' ?>>Pickup Truck</option>
    <option value="coupe" <?= $filters['category'] === 'coupe' ? 'selected' : '' ?>>Sports Car / Coupe</option>
    <option value="motorcycle" <?= $filters['category'] === 'motorcycle' ? 'selected' : '' ?>>Motorcycle</option>
</select>

<label for="transmission">Transmission</label>
<select id="transmission" name="transmission">
    <option value="">Any</option>
    <option value="automatic" <?= $filters['transmission'] === 'automatic' ? 'selected' : '' ?>>Automatic</option>
    <option value="manual" <?= $filters['transmission'] === 'manual' ? 'selected' : '' ?>>Manual</option>
</select>

<label for="min_price">Min Daily Rate</label>
<input type="number" id="min_price" name="min_price" step="0.01" min="0" value="<?= isset($_GET['min_price']) ? e($_GET['min_price']) : '' ?>">

<label for="max_price">Max Daily Rate</label>
<input type="number" id="max_price" name="max_price" step="0.01" min="0" value="<?= isset($_GET['max_price']) ? e($_GET['max_price']) : '' ?>">

<label for="sort">Sort By</label>
<select id="sort" name="sort">
    <option value="featured" <?= $filters['sort'] === 'featured' ? 'selected' : '' ?>>Recommended</option>
    <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
    <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
    <option value="make_asc" <?= $filters['sort'] === 'make_asc' ? 'selected' : '' ?>>Make (A-Z)</option>
</select>