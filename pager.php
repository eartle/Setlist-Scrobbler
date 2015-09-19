<nav>
    <ul class="pager">
        <?php
            $previousPage = $page - 1;
            $previousDisabled = $page == 0;
        ?>
        <li class="previous<?=$previousDisabled?' disabled':''?>">
            <a <?=$previousDisabled?"":"href=\"?page=$previousPage\""?>>
                <span aria-hidden="true">&larr;</span> Previous
            </a>
        </li>
        <?php
            $nextPage = $page + 1;
        ?>
        <li class="next<?=$nextDisabled?' disabled':''?>">
            <a <?=$nextDisabled?"":"href=\"?page=$nextPage\""?>>
                Next <span aria-hidden="true">&rarr;</span>
            </a>
        </li>
    </ul>
</nav>