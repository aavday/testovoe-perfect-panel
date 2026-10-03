# Тестовое задание для компании Perfect Panel

## Задание 1

### Инициализация докер-контейнера с MySQL для задания 1

```docker run --name testovoe-perfect-panel -e MYSQL_ROOT_PASSWORD=password -p 3306:3306 -d mysql```

### Запросы для создания базы данных, таблиц и наполнению таблиц тестовыми данными для задания 1

```
CREATE DATABASE IF NOT EXISTS test_library;

USE DATABASE test_library;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50),
    birthday DATE
);

CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL
);

CREATE TABLE user_books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (book_id) REFERENCES books (id),
    get_date DATE NOT NULL,
    return_date DATE
);

INSERT INTO users (first_name, last_name, birthday)
VALUES
    ('Ivan', 'Ivanov', '2012-01-01'),
    ('Marina', 'Ivanova', '2011-03-01'),
    ('Petr', 'Petrov', '2013-02-20'),
    ('Vasya', 'Pupkin', '1990-01-01'),
    ('Oleg', 'Kotov', '2020-03-05'),
    ('Aleksey', 'Alekseyev', '2010-05-18'),
    ('Alexander', 'Petrov', '2012-03-02'),
    ('Ilya', 'Ilyin', '2014-05-24'),
    ('Vladimir', 'Vladimirov', '2013-06-09'),
    ('Danil', 'Danilov', '2015-07-25'),
    ('Vladislav', 'Vladislavov', '2014-06-05');

INSERT INTO books (name, author)
VALUES
    ('Romeo and Juliet', 'William Shakespeare'),
    ('Hamlet', 'William Shakespeare'),
    ('King Lear', 'William Shakespeare'),
    ('War and Peace', 'Leo Tolstoy'),
    ('Anna Karenina', 'Leo Tolstoy'),
    ('The Death of Ivan Ilyich', 'Leo Tolstoy'),
    ('To Kill a Mockingbird', 'Harper Lee'),
    ('Go Set a Watchman', 'Harper Lee'),
    ('The Land of Sweet Forever', 'Harper Lee'),
    ('The Catcher in the Rye', 'J. D. Salinger'),
    ('Nine Stories', 'J. D. Salinger'),
    ('Franny and Zooey', 'J. D. Salinger');

INSERT INTO user_books (user_id, book_id, get_date, return_date)
VALUES
    (1, 1, '2026-09-20', '2026-09-25'),
    (1, 2, '2026-09-25', '2026-10-02'),
    (2, 7, '2026-06-10', '2026-06-17'),
    (2, 8, '2026-06-17', '2026-06-23'),
    (3, 4, '2026-01-12', '2026-02-17'),
    (3, 5, '2026-02-17', '2026-04-25'),
    (4, 1, '2026-09-13', '2026-09-23'),
    (4, 2, '2026-09-23', '2026-09-30'),
    (5, 8, '2026-08-21', '2026-08-28'),
    (5, 9, '2026-08-28', '2026-09-06'),
    (6, 2, '2026-07-13', '2026-07-22'),
    (6, 3, '2026-07-22', '2026-07-29'),
    (6, 10, '2026-08-29', '2026-09-08'),
    (7, 10, '2026-08-24', '2026-08-30'),
    (7, 11, '2026-08-30', '2026-09-08'),
    (7, 12, '2026-09-08', '2026-09-18'),
    (8, 1, '2026-08-12', '2026-08-20'),
    (8, 12, '2026-08-20', '2026-08-29'),
    (9, 10, '2026-07-12', '2026-07-19'),
    (10, 5, '2026-09-15', '2026-09-25'),
    (10, 4, '2026-09-25', null);
```

### Решение задания 1

Запрос на получение всех пользователей, которые брали ровно 2 книги и обе одного автора, при этом пользователям от 7 до 17 лет и они соблюдали 14-дневный срок пользования книгами:

```
SELECT
    CONCAT(u.first_name, ' ', u.last_name) AS name,
    MIN(b.author) as author,
    GROUP_CONCAT(b.name SEPARATOR ', ') AS books
FROM users AS u
    INNER JOIN user_books AS ub
        ON ub.user_id = u.id
    INNER JOIN books AS b
        ON b.id = ub.book_id
WHERE
    TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 7 AND 17
    AND DATEDIFF(ub.return_date, ub.get_date) <= 14
GROUP BY
    u.id
HAVING COUNT(*) = 2 AND COUNT(DISTINCT b.author) = 1;
```

Если выполнены запросы для наполнения таблиц тестовыми данными, то мы получим от вышеуказанного запроса следующий ответ:

| name           | author              | books                                    |
|----------------|---------------------|------------------------------------------|
| Ivan Ivanov    | William Shakespeare | Romeo and Juliet, Hamlet                 |
| Marina Ivanova | Harper Lee          | To Kill a Mockingbird, Go Set a Watchman |

### Проверка задания 1

С помощью нижеуказанного запроса можно получить более подробную выдачу данных по каждому пользователю для сверки:

```
SELECT
    u.id,
    u.first_name,
    u.last_name,
    u.birthday,
    TIMESTAMPDIFF(YEAR, u.birthday, '2026-10-03') AS age,
    b.author,
    COUNT(b.id) AS books_count,
    GROUP_CONCAT(b.name SEPARATOR ', ') AS books,
    GROUP_CONCAT(DATEDIFF(ub.return_date, ub.get_date) SEPARATOR ', ') AS days_in_hands
FROM users AS u
         LEFT JOIN user_books AS ub
              ON ub.user_id = u.id
         LEFT JOIN books AS b
              ON b.id = ub.book_id
GROUP BY
    u.id,
    u.first_name,
    u.last_name,
    u.birthday,
    b.author;
```

Этот запрос выдает следующий результат:

| id | first_name | last_name   | birthday   | age | author              | books_count | books                                                  | days_in_hands |
|----|------------|-------------|------------|-----|---------------------|-------------|--------------------------------------------------------|---------------|
| 1  | Ivan       | Ivanov      | 2012-01-01 | 14  | William Shakespeare | 2           | Hamlet, Romeo and Juliet                               | 7, 5          |
| 2  | Marina     | Ivanova     | 2011-03-01 | 15  | Harper Lee          | 2           | To Kill a Mockingbird, Go Set a Watchman               | 7, 6          |
| 3  | Petr       | Petrov      | 2013-02-20 | 13  | Leo Tolstoy         | 2           | War and Peace, Anna Karenina                           | 36, 67        |
| 4  | Vasya      | Pupkin      | 1990-01-01 | 36  | William Shakespeare | 2           | Romeo and Juliet, Hamlet                               | 10, 7         |
| 5  | Oleg       | Kotov       | 2020-03-05 | 6   | Harper Lee          | 2           | Go Set a Watchman, The Land of Sweet Forever           | 7, 9          |
| 6  | Aleksey    | Alekseyev   | 2010-05-18 | 16  | J. D. Salinger      | 1           | The Catcher in the Rye                                 | 10            |
| 6  | Aleksey    | Alekseyev   | 2010-05-18 | 16  | William Shakespeare | 2           | Hamlet, King Lear                                      | 9, 7          |
| 7  | Alexander  | Petrov      | 2012-03-02 | 14  | J. D. Salinger      | 3           | The Catcher in the Rye, Nine Stories, Franny and Zooey | 6, 9, 10      |
| 8  | Ilya       | Ilyin       | 2014-05-24 | 12  | J. D. Salinger      | 1           | Franny and Zooey                                       | 9             |
| 8  | Ilya       | Ilyin       | 2014-05-24 | 12  | William Shakespeare | 1           | Romeo and Juliet                                       | 8             |
| 9  | Vladimir   | Vladimirov  | 2013-06-09 | 13  | J. D. Salinger      | 1           | The Catcher in the Rye                                 | 7             |
| 10 | Danil      | Danilov     | 2015-07-25 | 11  | Leo Tolstoy         | 2           | Anna Karenina, War and Peace                           | 10            |
| 11 | Vladislav  | Vladislavov | 2014-06-05 | 12  | null                | 0           | null                                                   | null          |

Исходя из этой таблицы видно, что:

1. Иван действительно брал 2 книги, обе одного автора, возраст подходит, двухнедельный срок соблюден.
2. Марина действительно брала 2 книги, обе одного автора, возраст подходит, двухнедельный срок соблюден.
3. Петр действительно брал 2 книги, обе одного автора, возраст подходит, НО двухнедельный срок НЕ соблюден.
4. Вася действительно брал 2 книги, обе одного автора, НО возраст ВЫШЕ указанного, хотя двухнедельный срок соблюден.
5. Олег действительно брал 2 книги, обе одного автора, НО возраст НИЖЕ указанного, хотя двухнедельный срок соблюден.
6. Алексей действительно брал 2 книги, обе одного автора, НО брал еще одну книгу другого автора, хотя возраст подходит и двухнедельный срок соблюден
7. Александр брал НЕ 2, а сразу 3 книги одного автора, хотя возраст подходит и двухнедельный срок соблюден.
8. Илья брал 2 книги РАЗНЫХ авторов, хотя возраст подходит и двухнедельный срок соблюден.
9. Владимир брал всего ОДНУ книгу, хотя возраст подходит и двухнедельный срок соблюден.
10. Данил брал 2 книги одного автора, возраст подходит, двухнедельный срок соблюден у ОДНОЙ из книг, но вторая еще НЕ ВОЗВРАЩЕНА (в days_in_hands только одно значение, хотя в books_count 2 значения).
11. Владислав не брал ни одной книги.

Таким образом, Иван и Марина единственные, кто подходит по нашим условиям и это именно то, что выдает запрос, указанный в ответе на задачу.