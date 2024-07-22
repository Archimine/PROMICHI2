document.addEventListener('DOMContentLoaded', async () => {
    const adminCourseList = document.getElementById('admin-course-list');

    const response = await fetch('/api/courses.php');
    const courses = await response.json();

    courses.forEach(course => {
        const courseDiv = document.createElement('div');
        courseDiv.classList.add('course');
        courseDiv.innerHTML = `
            <h2>${course.title}</h2>
            <button class="generate" onclick="generateCourse('${course.id}')">Generate Course</button>
            <p>Waitlist: ${course.waitlist.length} students</p>
        `;
        adminCourseList.appendChild(courseDiv);
    });
});

async function generateCourse(courseId) {
    const response = await fetch(`/api/courses.php?action=generate&courseId=${courseId}`, {
        method: 'POST'
    });
    const result = await response.json();
    if (response.status === 400) {
        alert(result.message);
    } else {
        alert(`New course created: ${result.title}`);
    }
}
