<div class="field">
    <label for="classe_id">Visible par</label>
    <select id="classe_id" name="classe_id">
        <option value="tous">Tout le monde</option>
        <?php foreach ($classes as $c): ?>
            <option value="<?= (int) $c['id'] ?>"<?= (int) ($selected ?? 0) === (int) $c['id'] ? ' selected' : '' ?>>Classe : <?= e($c['nom']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'classe_id']) ?>
</div>
