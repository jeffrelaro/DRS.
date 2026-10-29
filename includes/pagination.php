<?php
function page_number(int $total, int $perPage): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page  = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => $pages]]);

    return [$page === false ? 1 : $page, $pages];
}

function page_link(int $page): string
{
    $query         = $_GET;
    $query['page'] = $page;
    return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($query);
}
