document.addEventListener('DOMContentLoaded', async () => {
    const courseList = document.getElementById('course-list');

    const response = await fetch('/api/courses.php');
    const courses = await response.json();

    courses.forEach(course => {
        const courseDiv = document.createElement('div');
        courseDiv.classList.add('course');
        courseDiv.innerHTML = `
            <h2>${course.title}</h2>
            <button class="enroll" onclick="enrollWaitlist('${course.id}')">Enroll in Waitlist</button>
        `;
        courseList.appendChild(courseDiv);
    });
});

async function enrollWaitlist(courseId) {
    const name = prompt('Enter your name:');
    const email = prompt('Enter your email:');

    if (name && email) {
        const response = await fetch(`/api/courses.php?action=enroll&courseId=${courseId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, email })
        });
        const result = await response.json();
        alert(result.message);
    }
}
