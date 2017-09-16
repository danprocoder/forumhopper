<?php
echo '<div id="pages">Pages:';

for ($i = 1; $i <= $pages; $i++)
{
    echo ' (';
    if ($i == $current_page) {
        echo "<b>$i</b>";
    } else {
        echo '<a href="'.$page_link.'&page='.$i.'">' . $i . '</a>';
    }
    echo ')';
}

echo '</div>';