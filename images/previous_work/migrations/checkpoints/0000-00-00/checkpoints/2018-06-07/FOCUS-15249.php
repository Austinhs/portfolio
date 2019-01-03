<?php

Migrations::depend('FOCUS-14843');
FocusUser::clearCache();
FocusUser::refreshViews();
