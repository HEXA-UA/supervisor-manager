$(document).on('ready pjax:success', function() {

    $(document).find('.processControl').on('click', function(event) {

        var processName = $(this).data('process-name'),

            actionType = $(this).data('action-type');

        $(this).button('loading');

        $.post('/supervisor/default/process-control', {
            processName: processName,
            actionType: actionType
        }, function(response) {

            if (response['isSuccessful']) {

                $.pjax.reload({container:'#supervisor', timeout: 2000});

                return true;
            }

            var modal = $('#errorModal');

            modal.find('.modal-body p').html(
                response.error
            );

            modal.modal();

            $(this).button('reset');
        });
    });

    $(document).find('.supervisorControl').on('click', function(event) {

        var actionType = $(this).data('action');

        if (actionType == 'refresh') {
            $.pjax.reload({container:'#supervisor', timeout: 2000});

            return;
        }

        $.post('/supervisor/default/supervisor-control', {
            actionType: actionType
        }, function(response) {

            if (response['isSuccessful']) {

                $.pjax.reload({container:'#supervisor', timeout: 2000});

                return true;
            }

            var logModal = $('#errorModal');

            logModal.find('.modal-body p').html(response['error']);

            logModal.modal();
        });
    });

    $(document).find('.groupControl [data-action]').on('click', function(event) {

        var actionUrl = '/supervisor/default/group-control';

        if ($(event.currentTarget).hasClass('processConfigControl')) {
            actionUrl = '/supervisor/default/process-config-control'
        }

        var actionType = $(this).data('action'),

            groupName = $(this).parents('.groupControl').data('groupName');

        $.post(actionUrl, {
            actionType: actionType,
            groupName: groupName
        }, function(response) {

            if (response['isSuccessful']) {

                $.pjax.reload({container:'#supervisor', timeout: 2000});

                return true;
            }

            var logModal = $('#errorModal');

            logModal.find('.modal-body p').html(response['error']);

            logModal.modal();
        });
    });

    $(document).find('.processList .showLog').on('click', function(event) {

        var processName = $(this).data('process-name'),

            logType = $(this).data('log-type');

        $.post('/supervisor/default/get-process-log', {
            processName: processName,
            logType: logType
        }, function(response) {

            var logModal = $('#errorModal'),

                message = null;

            if (response['isSuccessful']) {
                logModal = $('#processOutputModal');

                message = response['processLog'].replace(/\n/g, '<br>');
            } else {
                message = response['error'];
            }

            logModal.find('.modal-body p').html(message);

            logModal.modal();
        });
    });
});
