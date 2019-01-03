<?php

Database::begin();

Database::dropColumn('referral_actions', 'profiles_view');

Database::commit();