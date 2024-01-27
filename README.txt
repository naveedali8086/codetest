Do at least ONE of the following tasks: refactor is mandatory. Write tests is optional, will be good bonus to see it.
Please do not invest more than 2-4 hours on this.
Upload your results to a Github repo, for easier sharing and reviewing.

Thank you and good luck!



Code to refactor
=================
1) app/Http/Controllers/BookingController.php
2) app/Repository/BookingRepository.php

Code to write tests (optional)
=====================
3) App/Helpers/TeHelper.php method willExpireAt
4) App/Repository/UserRepository.php, method createOrUpdate


----------------------------

What I expect in your repo:

X. A readme with:   Your thoughts about the code. What makes it amazing code. Or what makes it ok code. Or what makes it terrible code. How would you have done it. Thoughts on formatting, structure, logic.. The more details that you can provide about the code (what's terrible about it or/and what is good about it) the easier for us to assess your coding style, mentality etc

And 

Y.  Refactor it if you feel it needs refactoring. The more love you put into it. The easier for us to asses your thoughts, code principles etc


IMPORTANT: Make two commits. First commit with original code. Second with your refactor so we can easily trace changes. 


NB: you do not need to set up the code on local and make the web app run. It will not run as its not a complete web app. This is purely to assess you thoughts about code, formatting, logic etc


===== So expected output is a GitHub link with either =====

1. Readme described above (point X above) + refactored code 
OR
2. Readme described above (point X above) + refactored core + a unit test of the code that we have sent

Thank you!


|--------------------------------------------------------------------------
| My Thoughts
|--------------------------------------------------------------------------
|
| I am writing my opinion for following two points as I did not like the code
| at all so considering good or ok does not make to me (sorry about that but
| this is what is)
| 1. what makes it terrible code.
| 2. How would you have done it.
|

2. No code segregation was used. Everything was just from Controller to Repository i.e.
- Form request may be used for validation and data modification if needed
- No Policy class was used for authorization
- No $cast property or accessor/mutators were defined for job model and all was being achieved using if-else

2. Queues were not used for operations that will delay response
i.e. sending mails, sending notifications to tens of users

3. Database transactions were not used where it make sense like update method of BookingController,
leading to inconsistent data

4. Inconsistent response format from controllers/repository

4. String based constants does not have single source of truth, they are hardcoded everywhere in code, instead
Enums could be utilized. I have added for understanding (obviously they are incomplete), consequently making
code difficult to read, understand and manage

5. Try/Catch were almost missed neither the Handler.php was found that is handling all exceptions in app
and generating consistent response

6. Too much redundant code was found for getting jobs i.e.
messages should be set in translation files

7. No use of laravel form requests was found and all the needed data modification was done right in repository
method i.e. store() of booking controller

8. It does not make sense to use $mode->all() on such models whose data is not static. Server may go down
for example, if we have thousands of jobs in DB and the system is experiencing more users

9. Roles should be defined in DB or Enum

10. No authorization checks / very few were found on all methods of the controller

11. App level variables should not read from .env(). For such purpose config files are best as they can not be
modified by system as well as allow caching

12. Some methods are very much large i.e. update() of BookingController, that is making it very difficult
to understand

13. Naming convention is als not good at some places i.e. cancelJobAjax

14. So much if-else nesting was used in some of the methods, making them very difficult to understand and work with

15. No activity logs were maintained i.e. who did the action, what was the model state before and after the operation

16. Messages were hardcode instead of defined in translation files

This is not all, there are many other points that must be considered and I might have missed them. Would love
to get your feedback too

P.S. I love writing test cases and to write the test cases (even a single one) requires to have complete knowledge
from database table (that represent the model), the model itself, model factory and other checks as per the business
logic.


