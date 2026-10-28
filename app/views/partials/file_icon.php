<?php
// Icône colorée selon le type de fichier ; $name = nom du fichier, $small = version compacte.
$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
$kind = match (true) {
    $ext === 'pdf' => 'pdf',
    in_array($ext, ['doc', 'docx', 'odt'], true) => 'word',
    in_array($ext, ['xls', 'xlsx', 'ods'], true) => 'sheet',
    in_array($ext, ['ppt', 'pptx', 'odp'], true) => 'slides',
    in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) => 'image',
    in_array($ext, ['mp3', 'm4a'], true) => 'audio',
    $ext === 'mp4' => 'video',
    default => 'text',
};
$paths = [
    'pdf'    => 'M7.5 10.5h7M7.5 13h7M7.5 15.5h4',
    'word'   => 'm7.5 11 1.5 6 1.5-4.5 1.5 4.5 1.5-6',
    'sheet'  => 'M7 11h8v6H7zm0 3h8m-4-3v6',
    'slides' => 'M7 11h8v5H7zm4 5v2',
    'image'  => 'm7 17 3-3.5 2 2 1.5-1.5L16 17Zm1.5-5a1 1 0 1 0 0-.01',
    'audio'  => 'M10 16.5V11l5-1.5v5.5M10 16.5a1.5 1.5 0 1 1-1.5-1.5 1.5 1.5 0 0 1 1.5 1.5Zm5-1.5a1.5 1.5 0 1 1-1.5-1.5A1.5 1.5 0 0 1 15 15',
    'video'  => 'm9.5 11 5 3-5 3Z',
    'text'   => 'M7.5 10.5h7M7.5 13h7M7.5 15.5h4',
];
?>
<span class="file-icon fi-<?= $kind ?><?= !empty($small) ? ' file-icon-sm' : '' ?>" aria-hidden="true">
    <svg viewBox="0 0 22 24"><path class="file-sheet" d="M4 2h9l5 5v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z"/><path class="file-fold" d="M13 2v5h5"/><path class="file-mark" d="<?= $paths[$kind] ?>"/></svg>
    <?php if (empty($small)): ?><span class="file-ext"><?= e(strtoupper($ext ?: '?')) ?></span><?php endif; ?>
</span>
