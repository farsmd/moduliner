<?php
/** صفحه اصلی سایت — متغیرها: $page, $pages */
ob_start();
?>
<article>
    <h1><?= htmlspecialchars($page['title']) ?></h1>
    <div class="body"><?= nl2br(htmlspecialchars($page['content'])) ?></div>
</article>
<?php
$content = ob_get_clean();
require __DIR__ . '/site_layout.php';
