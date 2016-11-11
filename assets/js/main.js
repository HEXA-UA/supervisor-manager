function SupervisorManager()
{
    /**
     * Initialize supervisor
     */
    this.init = function ()
    {
        this.setEventHandlers();
    };

    /**
     * Handle server response about supervisor control.
     *
     * @param response
     * @returns {boolean}
     */
    function responseHandler(response)
    {
        if (response['isSuccessful']) {
            $.pjax.reload({container:'#supervisor', timeout: 2000});

            return true;
        }

        var logModal = $('#errorModal');

        logModal.find('.modal-body p').html(response['error']);

        logModal.modal();
    }

    /**
     * Event handler for main supervisor process control.
     *
     * @param event
     */
    this.supervisorControl = function(event)
    {
        var actionType = $(this).data('action');

        if (actionType == 'refresh') {
            $.pjax.reload({container:'#supervisor', timeout: 2000});

            return;

        } else if(actionType == 'restart') {
            var doRestart = confirm('Restart supervisor? All processes will be killed');
            if(!doRestart) {
                return;
            }
        }

        $.post('/supervisor/default/supervisor-control', {
            actionType: actionType
        }, responseHandler);
    };

    /**
     * Event handler for all supervisor sub processes control.
     *
     * @param event
     */
    this.processControl = function(event)
    {
        var processName = $(this).data('process-name'),

            actionType = $(this).data('action-type');

        $.post('/supervisor/default/process-control', {
            processName: processName,
            actionType: actionType
        }, responseHandler);
    };

    /**
     * Event handler for group of supervisor processes.
     *
     * @param event
     */
    this.groupControl = function(event)
    {
        var actionUrl = '/supervisor/default/group-control';

        if ($(event.currentTarget).hasClass('processConfigControl')) {
            actionUrl = '/supervisor/default/process-config-control'
        }

        var actionType  = $(this).data('action'),
            groupName   = $(this).parents('.groupControl').data('groupName'),
            needConfirm = $(this).data('need-confirm');

        if (typeof needConfirm != 'undefined') {
            if (!confirm("Are you sure?")) {
                return;
            }
        }

        $.post(actionUrl, {
            actionType: actionType,
            groupName: groupName
        }, responseHandler);
    };

    this.showLog = function(event)
    {
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
    };

    this.setEventHandlers = function()
    {
        var self = this;

        $(document).on(
            'click', 'a.processControl', self.processControl
        ).on(
            'click', '.supervisorControl', self.supervisorControl
        ).on(
            'click', '.groupControl [data-action]', self.groupControl
        ).on(
            'click', '.processList .showLog', self.showLog
        );
    };
}

$(document).on('ready', function() {
    (new SupervisorManager()).init();
});
