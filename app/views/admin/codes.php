<?php partial('admin/_head', ['user' => $user, 'heading' => 'Codes d\'inscription', 'sub' => 'Chaque code sert une seule fois. Il est affiché en clair uniquement au moment de sa création.']) ?>
<section class="container page-pad">
    <div class="grid-sidebar">
        <form method="post" action="<?= url('admin/codes') ?>" class="form card">
            <?= csrf_field() ?>
            <h2>Générer des codes</h2>
            <?php if (count($roles) > 1): ?>
                <div class="field">
                    <label for="role">Pour</label>
                    <select id="role" name="role" data-role-select>
                        <?php foreach ($roles as $r): ?><option value="<?= e($r) ?>"><?= e(role_label($r)) ?></option><?php endforeach; ?>
                    </select>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'role']) ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="role" value="eleve">
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'role']) ?>
            <?php endif; ?>
            <div class="field" data-classe-field>
                <label for="classe_id">Classe de l'élève</label>
                <select id="classe_id" name="classe_id">
                    <?php if (!$classes): ?><option value="">Aucune classe créée</option><?php endif; ?>
                    <?php foreach ($classes as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['nom']) ?></option><?php endforeach; ?>
                </select>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'classe_id']) ?>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="number" min="1" max="100" value="1" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'nombre']) ?>
                </div>
                <div class="field">
                    <label for="jours">Valable (jours)</label>
                    <input id="jours" name="jours" type="number" min="1" max="365" value="30">
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'jours']) ?>
                </div>
            </div>
            <div class="field">
                <label for="label">Note <span class="muted">(facultatif, ex. « Famille Benali »)</span></label>
                <input id="label" name="label" maxlength="150">
            </div>
            <button class="btn btn-primary btn-block" type="submit">Générer</button>
        </form>

        <div>
            <nav class="tabs" aria-label="Filtrer les codes">
                <?php foreach (['disponibles' => 'Disponibles', 'utilises' => 'Utilisés', 'tous' => 'Tous'] as $k => $label): ?>
                    <a href="?etat=<?= $k ?>"<?= $filter === $k ? ' aria-current="page"' : '' ?>><?= $label ?></a>
                <?php endforeach; ?>
            </nav>
            <?php if (!$codes): ?>
                <div class="empty"><p>Aucun code dans cette liste.</p></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Code</th><th>Pour</th><th>État</th><th class="right"></th></tr></thead>
                        <tbody>
                        <?php foreach ($codes as $c):
                            $state = $c['used_at'] ? 'Utilisé par ' . ($c['utilise_par'] ?? '?')
                                : ($c['revoked_at'] ? 'Annulé'
                                : ($c['expires_at'] && $c['expires_at'] < $now ? 'Expiré'
                                : ($c['expires_at'] ? 'Expire le ' . format_date($c['expires_at']) : 'Sans expiration'))); ?>
                            <tr>
                                <td><code><?= e($c['code_hint']) ?>-••••-••••</code><?php if ($c['label']): ?><br><span class="muted small"><?= e($c['label']) ?></span><?php endif; ?></td>
                                <td><?= e(role_label($c['role'])) ?><?= $c['classe_nom'] ? '<br><span class="muted small">' . e($c['classe_nom']) . '</span>' : '' ?></td>
                                <td class="small"><?= e($state) ?></td>
                                <td class="right">
                                    <?php if (!$c['used_at'] && !$c['revoked_at'] && in_array($c['role'], $roles, true)): ?>
                                        <form method="post" action="<?= url('admin/codes/' . (int) $c['id'] . '/revoquer') ?>" class="inline" data-confirm="Annuler ce code ?">
                                            <?= csrf_field() ?><button class="btn btn-ghost btn-sm">Annuler</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
