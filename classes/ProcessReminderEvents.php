<?php
include_once 'DataAccess.class.php';
include_once 'GamingHandler.class.php';
include_once 'Logger.class.php';
include_once 'Constants.class.php';
include_once 'EventReminderData.class.php';

$dataAccess = new DataAccess();
$loggerDataAccess = new DataAccess();
$logger = new Logger($loggerDataAccess);
$gamingHandler = new GamingHandler();

// Poll next 24 hours' worth of events, getting the next scheduled event for 
//  each user who has one or more scheduled events in that time range
$eventListByUser = $gamingHandler->GetListNextScheduledGamesByUser($dataAccess, $logger);

// Insert events into reminder email batch table, for later inclusion in an email batch
$gamingHandler->QueueReminderEmailBatch($dataAccess, $logger, $eventListByUser);

// Poll reminder email batch table, starting with oldest reminder, and send emails
$gamingHandler->SendNextReminderEmailBatch($dataAccess, $logger);