Test multi-user REST API service for creating a collection of programming tasks for every day.

Task categories:

- Fundamentals
- String
- Algorithms
- Mathematical
- Performance
- Booleans
- Functions

A task can only belong to one category

Once a day, a unique selection is formed for each user without repetition

API methods:

- User registration/authorization/refresh user token:

  ● POST /api/registration

    request parameters:

      {
         name (string, required): Name of user,
         email (string, required): User's email address,
         password (string, required): User's password,
         password_confirmation (string, required): User's password confirmation
      }

  ● POST /api/login

    request parameters:

      {
         email (string, required): User's email address,
         password (string, required): User's password
      }

  ● GET /api/refresh

    request parameters:

      Authorization token

- The method to get the job collection for the user:

  ● GET /api/get_selection

    request parameters:

      Authorization token

- Method for marking a task as completed:

  ● POST /api/tick_task

    request parameters:

      Authorization token

      {
        userId (integer, required): User id,
        taskId (integer, required): Task id
      }

- The method of replacing a task with another one:

  ● POST /api/change_task

    request parameters:

      Authorization token

      {
         userId (integer, required): User id,
         taskId (integer, required): Task id
      }

Response value:

{
   status (string): Status of request operation,
   message (string): Operation details,
   data (array): Main result of success operation,
   code (integer): Response code
}
