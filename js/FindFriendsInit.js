var availUsersTableLoadAction = 'GetFriendInviteAvailUsersForJTable';
var availUsersJTableDiv = "#searchForFriendsContent";

var manageFriendsListTableLoadAction = 'GetCurrentFriendsListForJTable';
var manageFriendsListJTableDiv = "#manageFriendsListContent";

var gamerTagViewerDlg = "gamerTagViewerDlg";
var gamerTagViewerJTableDiv = "#viewGamerTagsDiv";

function FindFriendsOnReady()
{
    LoadAvailUsersForFriendInvite();
    LoadCurrentFriendListForUser();
}

function LoadAvailUsersForFriendInvite()
{
    // Initialize jTable on availUsersJTableDiv div
    $(availUsersJTableDiv).jtable({
        title: "Available Users",
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: true,
        pageList: 'minimal',
        sorting: true,
        defaultSorting: 'UserName ASC',
        openChildAsAccordion: false,
        selecting: true,
        multiselect: true,
        selectingCheckboxes: true,
        selectOnRowClick: false,
        toolbar: {
            items:
            [
                {
                    text: 'Invite Selected',
                    icon: 'images/envelope_closed.png',
                    tooltip: 'Invite selected users to be your friend',
                    click: function(){
                        IssueFriendInviteToSelectedUsers();
                    }
                },
                {
                    text: 'Refresh',
                    icon: 'images/refresh.png',
                    tooltip: 'Refreshes list of available users',
                    click: function(){
			var fullRefresh = false;
                        ReloadAvailUsersTable(fullRefresh);
                    }
                }
            ]
        },
        actions: {
            listAction: "AJAXHandler.php"
        },
        fields: {
            ID: {
                key: true,
                list: false
            },
            UserName: {
                title: 'Username',
                width: '20%',
                sorting: true
            },
            FirstName: {
                title: 'First Name',
                width: '17%',
                sorting: true
            },
            LastName: {
                title: 'Last Name',
                width: '23%',
                sorting: true
            },
            Gender: {
                title: 'Gender',
                width: '10%',
                sorting: true
            },
            GamerTags: {
                title: 'View Gamer Tags',
                width: '15%',
                sorting: false,
                display: function (data) {
                    var $tagViewerLink = $('<a href="#" class="actionLink" id="tagsLink' + data.record.ID + '">Show Tags</a>');

                    $tagViewerLink.click(function () {
                        OpenGamerTagViewer(gamerTagViewerDlg, gamerTagViewerJTableDiv.substring(1), "Gamer Tag Viewer", 
                                           "Gamer Tags For: " + (data.record.FirstName + " " + data.record.LastName), true, true, data.record.ID);
                        return false;
                    });

                    // Return link for display in jTable
                    return $tagViewerLink;
                }
            },
            SendInvite: {
                title: 'Send Invite',
                width: '15%',
                display: function (data) {
                    var $inviteImage = $('<img alt="Invite" title="Invite this user to be your friend" src="images/envelope_closed.png" />');
                    $inviteImage.click(function () {
                        var userIds = [data.record.ID];
                        SendFriendInviteToUsers(userIds);
                    });

                    // Return image for display in jTable
                    return $inviteImage;
                },
                sorting: false,
                columnSelectable: false
            }
        },
	recordsLoaded: function(event, data) {
            $(availUsersJTableDiv + ' .jtable-data-row').each(function() {
		var id = $(this).attr('data-record-key');
		var dataRecordArray = $.grep(data.records, function (e) {
                    return e.ID === id;
                });
					
		//var playerData = dataRecordArray[0].PlayersSignedUpData;
                
            });
			
            var curWidthClass = GetCurWidthClass();
            //if(curWidthClass != 'desktop')  FormatAvailUserTableForCurrentView(true, curWidthClass);
	}
    });

    // Load available user list
    var postData = 
        {
            action: availUsersTableLoadAction
        };
		
    $(availUsersJTableDiv).jtable('load', postData);
}

function LoadCurrentFriendListForUser()
{
    // Initialize jTable on manageFriendsListJTableDiv div
    $(manageFriendsListJTableDiv).jtable({
        title: "Current Friends",
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: true,
        pageList: 'minimal',
        sorting: true,
        defaultSorting: 'UserName ASC',
        openChildAsAccordion: false,
        selecting: true,
        multiselect: true,
        selectingCheckboxes: true,
        selectOnRowClick: false,
        toolbar: {
            items:
            [
                {
                    text: 'Accept Selected',
                    icon: 'images/activate.png',
                    tooltip: 'Accepts any active invitations from selected users',
                    click: function(){
                        AcceptUserFriendInvites();
                    }
                },
                {
                    text: 'Remove Selected',
                    icon: 'images/delete.png',
                    tooltip: 'Removes selected users from your friend list, or cancels any active invitations',
                    click: function(){
                        RemoveUsersFromFriendList();
                    }
                },
                {
                    text: 'Refresh',
                    icon: 'images/refresh.png',
                    tooltip: 'Refreshes friend list',
                    click: function(){
			var fullRefresh = false;
                        ReloadManageFriendsTable(fullRefresh);
                    }
                }
            ]
        },
        actions: {
            listAction: "AJAXHandler.php",
            deleteAction: "AJAXHandler.php?action=RemoveUserFromFriendList"
        },
        fields: {
            ID: {
                key: true,
                list: false
            },
            UserName: {
                title: 'Username',
                width: '20%',
                sorting: true
            },
            FirstName: {
                title: 'First Name',
                width: '15%',
                sorting: true
            },
            LastName: {
                title: 'Last Name',
                width: '22%',
                sorting: true
            },
            InviteType: {
                title: 'Invite?',
                width: '8%',
                sorting: true
            },
            InviteReply: {
                title: 'Invite Reply',
                width: '10%',
                sorting: true
            },
            AnswerInvite: {
                title: 'Answer Invite',
                width: '12%',
                sorting: false,
                columnSelectable: false,
                display: function (data) {
                    if(data.record.InviteType == 'To Me') {
                        var $answerImage = $('<label><img alt="Accept" id="acc' + data.record.ID + '" class="acceptIcon" title="Accept this invite" src="images/activate.png" />&nbsp;&nbsp;' + 
                                                    '<img alt="Reject" id="rej' + data.record.ID + '" class="rejectIcon" title="Reject this invite" src="images/deactivate.png" /></label>');

                        // Return HTML element for display in jTable
                        return $answerImage;
                    }
                    else {
                        return $('<label>&nbsp;</label>');
                    }
                }
            },
            GamerTags: {
                title: 'View Gamer Tags',
                width: '13%',
                sorting: false,
                display: function (data) {
                    var $tagViewerLink = $('<a href="#" class="actionLink" id="tagsLink' + data.record.ID + '">Show Tags</a>');

                    $tagViewerLink.click(function () {
                        OpenGamerTagViewer(gamerTagViewerDlg, gamerTagViewerJTableDiv.substring(1), "Gamer Tag Viewer", 
                                           "Gamer Tags For: " + (data.record.FirstName + " " + data.record.LastName), true, true, data.record.ID);
                        return false;
                    });

                    // Return link for display in jTable
                    return $tagViewerLink;
                }
            }
        },
	recordsLoaded: function(event, data) {
            $(manageFriendsListJTableDiv + ' .jtable-data-row').each(function() {
		var id = $(this).attr('data-record-key');
				
                $(this).find('.acceptIcon').each(function() {
                    $(this).click(function () {
                        var userIds = [id];
                        SendInviteAccept(userIds);
                    });
		});
				
                $(this).find('.rejectIcon').each(function() {
                    $(this).click(function () {
                        var userIds = [id];
                        SendInviteReject(userIds);
                    });
		});
            });
			
            var curWidthClass = GetCurWidthClass();
            //if(curWidthClass != 'desktop')  FormatAvailUserTableForCurrentView(true, curWidthClass);
	}
    });

    // Load friend list
    var postData = 
        {
            action: manageFriendsListTableLoadAction
        };
		
    $(manageFriendsListJTableDiv).jtable('load', postData);
}

function ReloadAvailUsersTable(fullRefresh)
{
    if(fullRefresh) {
        //var searchFormData = ValidateSearchFormFields('.searchPanelAvailUserFilter', true);
        var postData = ('action=' + availUsersTableLoadAction);// + searchFormData.postData;
        $(availUsersJTableDiv).jtable('load', postData);
    }
    else {
        // Reload user list with same POST arguments
        $(availUsersJTableDiv).jtable('reload');
    }
}

function ReloadManageFriendsTable(fullRefresh)
{
    if(fullRefresh) {
        //var searchFormData = ValidateSearchFormFields('.searchPanelManageFriendsFilter', true);
        var postData = ('action=' + manageFriendsListTableLoadAction);// + searchFormData.postData;
        $(manageFriendsListJTableDiv).jtable('load', postData);
    }
    else {
        // Reload user list with same POST arguments
        $(manageFriendsListJTableDiv).jtable('reload');
    }
}

function IssueFriendInviteToSelectedUsers()
{
    var $selectedRows = $(availUsersJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        sweetAlert("No users selected");
        return;
    }

    var selectedUserIds = [];
    $selectedRows.each(function() {
            var id = $(this).data('record').ID;
            selectedUserIds.push(id);
        }
    );

    if(!SendFriendInviteToUsers(selectedUserIds)) {
        // Reject friend invitation request (de-select rows)
        DeselectAllJTableRows(availUsersJTableDiv);
    }
}

function AcceptUserFriendInvites()
{
    var $selectedRows = $(manageFriendsListJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        sweetAlert("No users selected");
        return;
    }

    var selectedUserIds = [];
    $selectedRows.each(function() {
            var id = $(this).data('record').ID;
            selectedUserIds.push(id);
        }
    );

    if(!SendInviteAccept(selectedUserIds)) {
        // Reject invitation accept request (de-select rows)
        DeselectAllJTableRows(manageFriendsListJTableDiv);
    }
}

function RemoveUsersFromFriendList()
{
    var $selectedRows = $(manageFriendsListJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        sweetAlert("No users selected");
        return;
    }

    var selectedUserIds = [];
    $selectedRows.each(function() {
            var id = $(this).data('record').ID;
            selectedUserIds.push(id);
        }
    );

    if(!SendInviteReject(selectedUserIds)) {
        // Reject invitation rejection request (de-select rows)
        DeselectAllJTableRows(manageFriendsListJTableDiv);
    }
}

function SendFriendInviteToUsers(userIDs)
{
    sweetAlert({
      title: "Confirm Invite",
      text: "Are you sure you want to send a friend invite to all selected users?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to send friend invites to selected users
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=SendFriendInviteToUsers&" + $.param({'userIds': userIDs}),
            success: function(response){
                var fullRefresh = false;
                ReloadAvailUsersTable(fullRefresh);
                
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Invitations Not Sent", response, "error");
                }
                else {
                    // Show success message
                    sweetAlert("Invitations Sent!", response, "success");
                }
            },
            error: function() {
		sweetAlert("Invitations Not Sent", "Unable to send friend invites to selected users: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Friend Invitations Canceled", "Your invitations have not been sent", "info");
      }
   });
}

function SendInviteAccept(userIDs)
{
    sweetAlert({
      title: "Confirm Acceptance",
      text: "Are you sure you want to accept the friend invitation from all selected users?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to accept invitations from selected users
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=AcceptUserFriendInvites&" + $.param({'userIds': userIDs}),
            success: function(response){
                var fullRefresh = false;
                ReloadManageFriendsTable(fullRefresh);
                
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Could Not Accept Invitations", response, "error");
                }
                else {
                    // Show success message
                    sweetAlert("Invitations Accepted!", response, "success");
                }
            },
            error: function() {
		sweetAlert("Could Not Accept Invitations", "Unable to accept invitations from selected users: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Invitation Acceptance Canceled", "Your Friends List Has Not Been Changed", "info");
      }
   });
}

function SendInviteReject(userIDs)
{
    sweetAlert({
      title: "Confirm Removal",
      text: "Are you sure you want to remove all selected users from your friends list, including invites from users who want to be your friend?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to remove selected users from friends list
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=RemoveUsersFromFriendList&" + $.param({'userIds': userIDs}),
            success: function(response){
                var fullRefresh = false;
                ReloadManageFriendsTable(fullRefresh);
                
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Could Not Remove Users", response, "error");
                }
                else {
                    // Show success message
                    sweetAlert("Users Removed", response, "success");
                }
            },
            error: function() {
		sweetAlert("Could Not Remove Users", "Unable to remove selected users from friends list: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Users Not Removed", "Your friends list has not been changed", "info");
      }
   });
}