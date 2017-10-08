# Finance Manager

Web application written in Nette framework allowing you to track your spending habits by letting you add categorized expenses of what you spend your money on.

You can also assign a priority to each expense. By doing that the application helps you to optimize your finances by overviewing what total amount of money is assigned to priorities in a pie chart. If you spend lots of money on low priority items, it's a sign to cut the expenses in that area. Beside that the app also visualises what categories you spend money in a pie chart too.

Not only you can add your expenses to app but there's also a possibility to define a monthly budget. Upon that the app will calculate maximum recommended amount of money that can be spent daily. 

Another useful feature would be periodic payments that will be automaticly added to the expenses each month. By setting periodic payments, you don't have to add the payments each month manually.

![alt text](https://martyhora.cz/img/portfolio/thumbnails/2.png)

# Installation

- clone project by running ```git clone https://github.com/martyhora/finance-manager.git``` and set www folder as DocumentRoot
- run ```composer install``` in the project root
- run ```npm install``` in the project root
- run ```bower install``` in the project root
- run ```gulp``` to compile changes in JS a SASS files (as the bundled versions of JS and CSS are included, this step is optional)
- create database and run SQL scripts in ```/app/sql/db.sql``` to create database structure in it
- create ```app/config/config.local.neon``` and set up the the database connection
- open the project in the browser
