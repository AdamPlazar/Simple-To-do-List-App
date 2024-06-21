$(document).ready(function() {
    const $loginForm = $('#login-form');
    const $registerForm = $('#register-form');
    const $todoApp = $('#todo-app');

    // Change to register form
    $('#show-register-form').click(function() {
        $loginForm.hide();
        $registerForm.show();
    });

    // Change to login form
    $('#show-login-form').click(function() {
        $registerForm.hide();
        $loginForm.show();
    });

    // Login form submission
    $('#login').submit(function(event) {
        event.preventDefault();
        const username = $('#login-username').val();
        const password = $('#login-password').val();

        // Login request to auth.php
        $.post('auth.php', { action: 'login', username, password }, function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                loadTasks();
                $loginForm.hide();
                $todoApp.show();
            } else {
                alert(data.error);
            }
        });
    });

    // Register form submission
    $('#register').submit(function(event) {
        event.preventDefault();
        const username = $('#register-username').val();
        const password = $('#register-password').val();

        // Register request to auth.php
        $.post('auth.php', { action: 'register', username, password }, function(response) {
            const data = JSON.parse(response);
            if (data.success) {

                // Login after successful registration
                $.post('auth.php', { action: 'login', username, password }, function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        loadTasks();
                        $registerForm.hide();
                        $todoApp.show();
                    }
                });
            } else {
                alert(data.error);
            }
        });
    });

    // Function to load tasks from Database
    function loadTasks() {
        $.get('api.php', function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                $('#task-list').empty();
                data.tasks.forEach(task => {
                    const li = $('<li>').text(task.task).data('id', task.id);
                    if (task.is_completed) {
                        li.addClass('checked');
                    }
                    const span = $('<span>').text('×');
                    li.append(span);
                    $('#task-list').append(li);
                });
            }
        });
    }

    // Function for adding new tasks
    function addTask() {
        const task = $('#task-input').val();
        if (task === '') {
            alert("You must write something!");
        } else {
            // Add task request to api.php
            $.post('api.php', { action: 'add', task }, function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    loadTasks();
                } else {
                    alert(data.error);
                }
            });
        }
        $('#task-input').val(""); // Clearing task input
    }

    $('#task-list').on('click', 'li', function() {
        const taskId = $(this).data('id');
        $.post('api.php', { action: 'complete', task_id: taskId }, function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                loadTasks();
            } else {
                alert(data.error);
            }
        });
    });

    // Delete task
    $('#task-list').on('click', 'span', function(e) {
        e.stopPropagation();
        const taskId = $(this).parent().data('id');
        $.post('api.php', { action: 'delete', task_id: taskId }, function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                loadTasks();
            } else {
                alert(data.error);
            }
        });
    });

    $('#add-task-button').on('click', addTask);

    //Log out button
    $('#logout-button').on('click', function() {
        $.post('auth.php', { action: 'logout' }, function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                $todoApp.hide();
                $loginForm.show();
            } else {
                alert('Logout failed: ' + data.error);
            }
        }).fail(function() {
            alert('Error logging out.');
        });
    });
});

