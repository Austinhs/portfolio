<?php

Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-5468');

// Schedule a cron job to parse addresses
ParseAddresses::schedule();
