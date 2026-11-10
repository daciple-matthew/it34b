<?php

//Database Connection
$host = 'localhost';
$db = 'library_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Session
session_start();

// Determine current section
$section = $_GET['section'] ?? 'students';

// Determine CRUD Operation
$action = $_GET['action'] ?? '';


// Fetch Students
if ($section === 'students') {
    $stmt = $pdo->query("
        SELECT *
        FROM students
        ORDER BY student_id DESC
    ");

    $students = $stmt->fetchAll();
}

// Create Student
if ($section === 'students' && $action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim($_POST['student_first_name'] ?? '');
        $lastName = trim($_POST['student_last_name'] ?? '');
        $course = trim($_POST['student_course'] ?? '');

        if ($firstName !== '' && $lastName !== '' && $course !== '') {
            $sql = "
                INSERT INTO students(
                    student_first_name,
                    student_last_name,
                    student_course
                )
                VALUES (?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $firstName,
                $lastName,
                $course
            ]);

            $_SESSION['alert'] = 'Student created successfully.';
            header("Location: index.php?section=students");
            exit;
        }
    }
}

// Update Student
if ($section === 'students' && $action === 'update') {
    $studentId = (int) ($_GET['id'] ?? 0);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim($_POST['student_first_name'] ?? '');
        $lastName = trim($_POST['student_last_name'] ?? '');
        $course = trim($_POST['student_course'] ?? '');

        $sql = "
            UPDATE students
            SET student_first_name = ?,
                student_last_name = ?,
                student_course = ?
            WHERE student_id = ?
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $firstName,
            $lastName,
            $course,
            $studentId
        ]);

        $_SESSION['alert'] = 'Student updated successfully.';
        header("Location: index.php?section=students");
        exit;
    }

    // Retrieve student information
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();

    if (!$student) {
        die("Student not found");
    }
}


// Fetch Books
if ($section === 'books') {
    $stmt = $pdo->query("
        SELECT *
        FROM books
        ORDER BY book_id DESC
    ");

    $books = $stmt->fetchAll();
}

// Create Book
if ($section === 'books' && $action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['book_title'] ?? '');
        $author = trim($_POST['book_author'] ?? '');
        $category = trim($_POST['book_category'] ?? '');

        if ($title !== '' && $author !== '' && $category !== '') {
            $sql = "
                INSERT INTO books(
                    book_title,
                    book_author,
                    book_category
                )
                VALUES (?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $title,
                $author,
                $category
            ]);

            $_SESSION['alert'] = 'Book created successfully.';
            header("Location: index.php?section=books");
            exit;
        }
    }
}

// Update Book
if ($section === 'books' && $action === 'update') {
    $bookId = (int) ($_GET['id'] ?? 0);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['book_title'] ?? '');
        $author = trim($_POST['book_author'] ?? '');
        $category = trim($_POST['book_category'] ?? '');

        $sql = "
            UPDATE books
            SET book_title = ?,
                book_author = ?,
                book_category = ?
            WHERE book_id = ?
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $title,
            $author,
            $category,
            $bookId
        ]);

        $_SESSION['alert'] = 'Book updated successfully.';
        header("Location: index.php?section=books");
        exit;
    }

    // Retrieve book information
    $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();

    if (!$book) {
        die("Book not found");
    }
}


// Borrow: fetch students and books for the dropdown menus
if ($section === 'borrow') {
    $stmt = $pdo->query("
        SELECT
            student_id,
            student_first_name,
            student_last_name
        FROM students
        ORDER BY student_last_name, student_first_name
    ");
    $students = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT
            book_id,
            book_title,
            book_author
        FROM books
        ORDER BY book_title
    ");
    $books = $stmt->fetchAll();
}

// Create Borrow Record
if ($section === 'borrow' && $action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $bookId = (int) ($_POST['book_id'] ?? 0);

        if ($studentId <= 0 || $bookId <= 0) {
            $_SESSION['alert'] = 'Please select a student and a book.';
        } else {
            // Check whether this student already has an unreturned book
            $stmt = $pdo->prepare("
                SELECT borrow_id
                FROM borrow
                WHERE student_id = ?
                  AND borrow_return_date IS NULL
                LIMIT 1
            ");
            $stmt->execute([$studentId]);
            $studentBorrow = $stmt->fetch();

            if ($studentBorrow) {
                $_SESSION['alert'] = 'This student cannot borrow another book because a previous book has not been returned.';
            } else {
                // Check whether the selected book is already borrowed
                $stmt = $pdo->prepare("
                    SELECT borrow_id
                    FROM borrow
                    WHERE book_id = ?
                      AND borrow_return_date IS NULL
                    LIMIT 1
                ");
                $stmt->execute([$bookId]);
                $bookBorrow = $stmt->fetch();

                if ($bookBorrow) {
                    $_SESSION['alert'] = 'This book is already borrowed and has not been returned.';
                } else {
                    // Save the borrow record
                    $stmt = $pdo->prepare("
                        INSERT INTO borrow (
                            student_id,
                            book_id
                        )
                        VALUES (?, ?)
                    ");

                    $stmt->execute([
                        $studentId,
                        $bookId
                    ]);

                    $_SESSION['alert'] = 'Book borrowed successfully.';
                }
            }
        }

        header("Location: index.php?section=borrow");
        exit;
    }
}

// Return a Borrowed Book
if ($section === 'borrow' && $action === 'return') {
    $borrowId = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare("
        UPDATE borrow
        SET borrow_return_date = NOW()
        WHERE borrow_id = ?
          AND borrow_return_date IS NULL
    ");
    $stmt->execute([$borrowId]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['alert'] = 'Book returned successfully.';
    } else {
        $_SESSION['alert'] = 'This book was already returned or the borrow record was not found.';
    }

    header("Location: index.php?section=borrow");
    exit;
}

// Fetch borrow records to display in the Borrow section
if ($section === 'borrow') {
    $stmt = $pdo->query("
        SELECT
            borrow.borrow_id,
            borrow.borrow_return_date,
            students.student_first_name,
            students.student_last_name,
            books.book_title,
            books.book_author
        FROM borrow
        INNER JOIN students
            ON borrow.student_id = students.student_id
        INNER JOIN books
            ON borrow.book_id = books.book_id
        ORDER BY borrow.borrow_id DESC
    ");

    $borrowRecords = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="table.css">
    <title>Library System</title>
</head>

<body>
    <h1>Simple Library System</h1>
     <a href="../../auth/signout.php">Sign Out</a>

    <nav>
        <a href="index.php?section=students">Students</a>
        <a href="index.php?section=books">Books</a>
        <a href="index.php?section=borrow">Borrow</a>
    </nav>

    <hr>

    <?php if ($section === 'students'): ?>
        <h1>Students</h1>

        <p>
            <a href="index.php?section=students&action=create">
                Add Student
            </a>
        </p>

        <?php if ($action === 'create'): ?>
            <h2>Create Student</h2>

            <form method="POST" action="index.php?section=students&action=create">
                <p>
                    <label>First Name:</label>
                    <br>
                    <input
                        type="text"
                        name="student_first_name"
                        required
                    />
                </p>

                <p>
                    <label>Last Name:</label>
                    <br>
                    <input
                        type="text"
                        name="student_last_name"
                        required
                    />
                </p>

                <p>
                    <label>Course:</label>
                    <br>
                    <input
                        type="text"
                        name="student_course"
                        required
                    />
                </p>

                <button type="submit">Save</button>

                <a href="index.php?section=students">Cancel</a>
            </form>

        <?php elseif ($action === 'update'): ?>
            <h2>Update Student Info</h2>

            <form method="POST" action="index.php?section=students&action=update&id=<?= (int) $student['student_id'] ?>">
                <p>
                    <label>First Name:</label>
                    <br>
                    <input
                        type="text"
                        name="student_first_name"
                        value="<?= htmlspecialchars($student['student_first_name']) ?>"
                        required
                    />
                </p>

                <p>
                    <label>Last Name:</label>
                    <br>
                    <input
                        type="text"
                        name="student_last_name"
                        value="<?= htmlspecialchars($student['student_last_name']) ?>"
                        required
                    />
                </p>

                <p>
                    <label>Course:</label>
                    <br>
                    <input
                        type="text"
                        name="student_course"
                        value="<?= htmlspecialchars($student['student_course']) ?>"
                        required
                    />
                </p>

                <button type="submit">Update</button>
                <a href="index.php?section=students">Cancel</a>
            </form>

        <?php else: ?>
            <table class="student-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>FIRST NAME</th>
                        <th>LAST NAME</th>
                        <th>COURSE</th>
                        <th>CREATED AT</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                            <td><?= htmlspecialchars($student['student_first_name']) ?></td>
                            <td><?= htmlspecialchars($student['student_last_name']) ?></td>
                            <td><?= htmlspecialchars($student['student_course']) ?></td>
                            <td><?= htmlspecialchars($student['student_created_at']) ?></td>
                            <td>
                                <a href="index.php?section=students&action=update&id=<?= (int) $student['student_id'] ?>">
                                    Edit
                                </a>
                                <a href="#">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>


    <hr>

    <?php if ($section === 'books'): ?>
        <h1>Books</h1>

        <p>
            <a href="index.php?section=books&action=create">
                Add Book
            </a>
        </p>

        <?php if ($action === 'create'): ?>
            <h2>Create Book</h2>

            <form method="POST" action="index.php?section=books&action=create">
                <p>
                    <label>Book Title:</label>
                    <br>
                    <input
                        type="text"
                        name="book_title"
                        required
                    />
                </p>

                <p>
                    <label>Book Author:</label>
                    <br>
                    <input
                        type="text"
                        name="book_author"
                        required
                    />
                </p>

                <p>
                    <label>Book Category:</label>
                    <br>
                    <input
                        type="text"
                        name="book_category"
                        required
                    />
                </p>

                <button type="submit">Save</button>
                <a href="index.php?section=books">Cancel</a>
            </form>

        <?php elseif ($action === 'update'): ?>
            <h2>Update Book Info</h2>

            <form method="POST" action="index.php?section=books&action=update&id=<?= (int) $book['book_id'] ?>">
                <p>
                    <label>Book Title:</label>
                    <br>
                    <input
                        type="text"
                        name="book_title"
                        value="<?= htmlspecialchars($book['book_title']) ?>"
                        required
                    />
                </p>

                <p>
                    <label>Book Author:</label>
                    <br>
                    <input
                        type="text"
                        name="book_author"
                        value="<?= htmlspecialchars($book['book_author']) ?>"
                        required
                    />
                </p>

                <p>
                    <label>Book Category:</label>
                    <br>
                    <input
                        type="text"
                        name="book_category"
                        value="<?= htmlspecialchars($book['book_category']) ?>"
                        required
                    />
                </p>

                <button type="submit">Update</button>
                <a href="index.php?section=books">Cancel</a>
            </form>

        <?php else: ?>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>BOOK TITLE</th>
                        <th>BOOK AUTHOR</th>
                        <th>BOOK CATEGORY</th>
                        <th>CREATED AT</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['book_id']) ?></td>
                            <td><?= htmlspecialchars($book['book_title']) ?></td>
                            <td><?= htmlspecialchars($book['book_author']) ?></td>
                            <td><?= htmlspecialchars($book['book_category']) ?></td>
                            <td><?= htmlspecialchars($book['book_created_at']) ?></td>
                            <td>
                                <a href="index.php?section=books&action=update&id=<?= (int) $book['book_id'] ?>">
                                    Edit
                                </a>
                                <a href="#">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>


    <?php if ($section === 'borrow'): ?>
        <h1>Borrow Books</h1>

        <p>
            <a href="index.php?section=borrow&action=create">
                Borrow Book
            </a>
        </p>

        <?php if ($action === 'create'): ?>
            <h2>Borrow a Book</h2>

            <form method="POST" action="index.php?section=borrow&action=create">
                <p>
                    <label>Select Student:</label>
                    <br>

                    <select name="student_id" required>
                        <option value="">-- Select Student --</option>

                        <?php foreach ($students as $student): ?>
                            <option value="<?= (int) $student['student_id'] ?>">
                                <?= htmlspecialchars(
                                    $student['student_first_name'] . ' ' .
                                    $student['student_last_name']
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label>Select Book:</label>
                    <br>

                    <select name="book_id" required>
                        <option value="">-- Select Book --</option>

                        <?php foreach ($books as $book): ?>
                            <option value="<?= (int) $book['book_id'] ?>">
                                <?= htmlspecialchars(
                                    $book['book_title'] . ' - ' .
                                    $book['book_author']
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <button type="submit">Confirm Borrow</button>
                <a href="index.php?section=borrow">Cancel</a>
            </form>
        <?php endif; ?>

        <h2>Borrow Records</h2>

         <table class="borrow-table">
            <thead>
                <tr>
                    <th>BORROW ID</th>
                    <th>STUDENT</th>
                    <th>BOOK</th>
                    <th>STATUS</th>
                    <th>RETURN DATE</th>
                    <th>ACTION</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($borrowRecords)): ?>
                    <tr>
                        <td colspan="6">No borrow records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($borrowRecords as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['borrow_id']) ?></td>

                            <td>
                                <?= htmlspecialchars(
                                    $record['student_first_name'] . ' ' .
                                    $record['student_last_name']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($record['book_title']) ?>
                            </td>

                            <td>
                                <?= $record['borrow_return_date'] === null
                                    ? 'BORROWED'
                                    : 'RETURNED' ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $record['borrow_return_date'] ?? 'NOT RETURNED YET'
                                ) ?>
                            </td>

                            <td>
                                <?php if ($record['borrow_return_date'] === null): ?>
                                    <a
                                        href="index.php?section=borrow&action=return&id=<?= (int) $record['borrow_id'] ?>"
                                        onclick="return confirm('Are you sure you want to return this book?');"
                                    >
                                        Return Book
                                    </a>
                                <?php else: ?>
                                    Returned
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>


    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            alert(<?= json_encode($_SESSION['alert']) ?>);
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

</body>
</html>
