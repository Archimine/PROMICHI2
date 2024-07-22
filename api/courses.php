<?php
include('../db.php');

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$courseId = $_GET['courseId'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'enroll') {
        $name = $data['name'];
        $email = $data['email'];

        $stmt = $conn->prepare("INSERT INTO waitlist (course_id, name, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $courseId, $name, $email);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['message' => 'Successfully enrolled in waitlist']);
    } elseif ($action === 'generate') {
        $stmt = $conn->prepare("SELECT * FROM waitlist WHERE course_id = ?");
        $stmt->bind_param("s", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $waitlist = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (count($waitlist) > 15) {
            $newCourseTitle = 'New Course for ' . $courseId;
            $stmt = $conn->prepare("INSERT INTO courses (title) VALUES (?)");
            $stmt->bind_param("s", $newCourseTitle);
            $stmt->execute();
            $newCourseId = $stmt->insert_id;
            $stmt->close();

            foreach ($waitlist as $student) {
                $stmt = $conn->prepare("INSERT INTO enrollments (course_id, name, email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $newCourseId, $student['name'], $student['email']);
                $stmt->execute();
            }

            $stmt = $conn->prepare("DELETE FROM waitlist WHERE course_id = ?");
            $stmt->bind_param("s", $courseId);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['title' => $newCourseTitle]);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Waitlist does not exceed 15 students']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT * FROM courses");
    $courses = $result->fetch_all(MYSQLI_ASSOC);

    foreach ($courses as &$course) {
        $stmt = $conn->prepare("SELECT * FROM waitlist WHERE course_id = ?");
        $stmt->bind_param("s", $course['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $course['waitlist'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    echo json_encode($courses);
}
?>
