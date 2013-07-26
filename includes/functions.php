<?php

add_filter( 'orbis_task_text', 'wptexturize' );
add_filter( 'orbis_task_text', 'convert_chars' );
add_filter( 'orbis_task_text', 'make_clickable', 9 );
add_filter( 'orbis_task_text', 'force_balance_tags', 25 );
add_filter( 'orbis_task_text', 'convert_smilies', 20 );
