/* =========================================================
   StudentHub - Master JavaScript File (Js/script.js)
   Contains all interactive functions and logic for portal pages
========================================================= */

// --- 1. Theme Management (Dark / Light Mode) ---
function changeTheme() {
    document.body.classList.toggle("dark");
    if (document.body.classList.contains("dark")) {
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
}

// Alias for pages calling theme()
function theme() {
    changeTheme();
}

// --- 2. Initial Page Setup on DOM Load ---
document.addEventListener("DOMContentLoaded", function() {
    // Apply saved theme
    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark");
    }

    // Index page date & welcome
    let dateEl = document.getElementById("date");
    if (dateEl) {
        let today = new Date();
        dateEl.innerHTML = "Today: " + today.toDateString();
    }
    let welcomeEl = document.getElementById("welcome");
    if (welcomeEl) {
        welcomeEl.innerHTML = "Welcome to Student Hub!";
    }

    // Admin page student count click
    let studentsEl = document.getElementById("students");
    if (studentsEl) {
        studentsEl.onclick = function() {
            studentsEl.innerHTML = "250 Students";
        };
    }

    // Assignment title character counter
    let titleEl = document.getElementById("title");
    if (titleEl) {
        titleEl.onkeyup = function() {
            let countEl = document.getElementById("count");
            if (countEl) countEl.innerHTML = "Characters: " + this.value.length + " / 50";
        };
    }

    // Assignment file upload preview
    let fileEl = document.getElementById("file");
    if (fileEl) {
        fileEl.onchange = function() {
            if (this.files.length > 0) {
                let fileNameEl = document.getElementById("fileName");
                if (fileNameEl) fileNameEl.innerHTML = "Selected file: " + this.files[0].name;
            }
        };
    }

    // Contact form message character counter
    let msgEl = document.getElementById("msg");
    if (msgEl) {
        msgEl.onkeyup = function() {
            let countEl = document.getElementById("count");
            if (countEl) countEl.innerHTML = "Characters: " + this.value.length + " / 100";
        };
    }
});

// Immediate execution for theme if loaded synchronously
if (localStorage.getItem("theme") === "dark" && document.body) {
    document.body.classList.add("dark");
}

// --- 3. Index Page Functions ---
function showMenu() {
    let menu = document.getElementById("menu");
    if (menu) {
        menu.style.display = (menu.style.display === "none") ? "block" : "none";
    }
}

function searchMenu() {
    let searchEl = document.getElementById("search");
    let menuEl = document.getElementById("menu");
    if (!searchEl || !menuEl) return;
    let input = searchEl.value.toLowerCase();
    let links = menuEl.getElementsByTagName("a");

    for (let i = 0; i < links.length; i++) {
        let text = links[i].innerHTML.toLowerCase();
        if (text.includes(input)) {
            links[i].style.display = "block";
        } else {
            links[i].style.display = "none";
        }
    }
}

// --- 4. Admin Panel Functions ---
function showMessage() {
    let notif = document.getElementById("notification");
    if (notif) notif.innerHTML = "Welcome Admin! You have 9 pending assignments.";
    let modal = document.getElementById("modal");
    if (modal) modal.style.display = "block";
}

function closeMessage() {
    let modal = document.getElementById("modal");
    if (modal) modal.style.display = "none";
}

function showFAQ() {
    let faq = document.getElementById("faq");
    if (!faq) return;
    faq.style.display = (faq.style.display === "block") ? "none" : "block";
}

// --- 5. Assignment Page Functions ---
function submitAssignment() {
    let name = document.getElementById("name") ? document.getElementById("name").value : "";
    let roll = document.getElementById("roll") ? document.getElementById("roll").value : "";
    let title = document.getElementById("title") ? document.getElementById("title").value : "";
    let date = document.getElementById("date") ? document.getElementById("date").value : "";
    let file = document.getElementById("file") ? document.getElementById("file").value : "";

    if (name === "" || roll === "" || title === "" || date === "" || file === "") {
        alert("Please fill all fields!");
        return;
    }

    let msg = document.getElementById("message");
    if (msg) {
        msg.style.display = "block";
        msg.innerHTML = "Assignment submitted successfully! ✅";
    }
}

function showHelp() {
    let help = document.getElementById("help");
    if (!help) return;
    help.style.display = (help.style.display === "block") ? "none" : "block";
}

// --- 6. Attendance Page Functions ---
function submitAttendance() {
    let name = document.getElementById("name") ? document.getElementById("name").value : "";
    let roll = document.getElementById("roll") ? document.getElementById("roll").value : "";
    let date = document.getElementById("date") ? document.getElementById("date").value : "";
    let status = document.getElementById("status") ? document.getElementById("status").value : "";

    if (name === "" || roll === "" || date === "" || status === "") {
        alert("Please fill all fields!");
        return;
    }

    let message = document.getElementById("message");
    if (message) {
        message.style.display = "block";
        message.innerHTML =
            "Attendance Submitted!<br>" +
            "Student: " + name + "<br>" +
            "Roll No: " + roll + "<br>" +
            "Status: " + status;
    }
}

// --- 7. Contact Page Functions ---
function sendMessage() {
    let name = document.getElementById("name") ? document.getElementById("name").value : "";
    let email = document.getElementById("email") ? document.getElementById("email").value : "";
    let subject = document.getElementById("subject") ? document.getElementById("subject").value : "";
    let msg = document.getElementById("msg") ? document.getElementById("msg").value : "";

    if (name === "" || email === "" || subject === "" || msg === "") {
        alert("Please fill all required fields!");
        return;
    }

    let message = document.getElementById("message");
    if (message) {
        message.style.display = "block";
        message.innerHTML = "Thank you " + name + "!<br>Message sent successfully! ";
    }
}

// --- 8. Courses Page Functions ---
function searchCourse() {
    let searchEl = document.getElementById("search");
    let coursesEl = document.getElementById("courses");
    if (!searchEl || !coursesEl) return;
    let input = searchEl.value.toLowerCase();
    let courses = coursesEl.getElementsByTagName("li");

    for (let i = 0; i < courses.length; i++) {
        let name = courses[i].innerHTML.toLowerCase();
        if (name.includes(input)) {
            courses[i].style.display = "block";
        } else {
            courses[i].style.display = "none";
        }
    }
}

function clearSearch() {
    let search = document.getElementById("search");
    if (search) search.value = "";
    searchCourse();
}

function selectCourse(course) {
    let message = document.getElementById("message");
    if (message) {
        message.style.display = "block";
        message.innerHTML = "You selected: <b>" + course.innerHTML + "</b>";
    }
}

// --- 9. Dashboard Page Functions ---
function showCourses() {
    let c = document.getElementById("courseMessage");
    if (c) c.innerHTML = "Total Courses: 3";
}

function showProgress() {
    let progress = document.getElementById("progress");
    let progressMsg = document.getElementById("progressMessage");
    if (progress && progressMsg) {
        progressMsg.innerHTML = "You have completed " + progress.innerHTML + " courses.";
    }
}

function showNotification() {
    let notification = document.getElementById("notification");
    if (!notification) return;
    notification.style.display = (notification.style.display === "block") ? "none" : "block";
}

function logout() {
    let result = confirm("Are you sure you want to logout?");
    if (result === true) {
        alert("Logout successful!");
        window.location.href = "login.html";
    }
}

// --- 10. Login Page Functions ---
function showPassword() {
    let p = document.getElementById("password");
    if (p) {
        p.type = (p.type === "password") ? "text" : "password";
    }
}

function login() {
    let usernameEl = document.getElementById("username");
    let passwordEl = document.getElementById("password");
    let message = document.getElementById("message");
    if (!usernameEl || !passwordEl || !message) return;

    let username = usernameEl.value.trim();
    let password = passwordEl.value.trim();

    if (username === "" || password === "") {
        message.innerHTML = "⚠️ Please enter all details.";
        message.className = "error";
        return;
    }

    let users = JSON.parse(localStorage.getItem("registeredUsers") || "[]");
    let foundUser = users.find(u => u.username === username && u.password === password);

    if ((username === "admin" && password === "12345") || (username === "student" && password === "12345") || foundUser || (username.length >= 3 && password.length >= 4)) {
        message.innerHTML = "✅ Login Successful! Redirecting to Home...";
        message.className = "success";
        localStorage.setItem("currentUser", username);
        setTimeout(function() {
            window.location.href = "index.html";
        }, 800);
    } else {
        message.innerHTML = "❌ Invalid Username or Password. (Try: admin / 12345)";
        message.className = "error";
    }
}

// --- 11. Profile Page Functions ---
function showDetails() {
    let x = document.getElementById("details");
    if (x) {
        x.style.display = (x.style.display === "none") ? "block" : "none";
    }
}

function back() {
    window.location.href = "dashboard.html";
}

// --- 12. Registration Page Functions ---
function register() {
    let nameEl = document.getElementById("name");
    let emailEl = document.getElementById("email");
    let phoneEl = document.getElementById("phone");
    let usernameEl = document.getElementById("username");
    let passwordEl = document.getElementById("password");
    let confirmEl = document.getElementById("confirm");
    let courseEl = document.getElementById("course");
    let genderEl = document.querySelector("input[name='gender']:checked");
    let msg = document.getElementById("message");

    if (!msg) return;
    msg.style.display = "block";

    let name = nameEl ? nameEl.value.trim() : "";
    let email = emailEl ? emailEl.value.trim() : "";
    let phone = phoneEl ? phoneEl.value.trim() : "";
    let username = usernameEl ? usernameEl.value.trim() : "";
    let password = passwordEl ? passwordEl.value : "";
    let confirm = confirmEl ? confirmEl.value : "";
    let course = courseEl ? courseEl.value : "";

    // 1. Check for empty fields
    if (!name || !email || !phone || !username || !password || !confirm) {
        msg.innerHTML = "⚠️ <b>Notice:</b> Please fill in all required fields.";
        msg.className = "alert-box alert-danger";
        return;
    }

    // 2. Full Name Regex (Only letters and spaces, 3-50 chars)
    let nameRegex = /^[A-Za-z\s]{3,50}$/;
    if (!nameRegex.test(name)) {
        msg.innerHTML = "⚠️ <b>Notice:</b> Full Name must contain only letters and spaces (min 3 characters).";
        msg.className = "alert-box alert-danger";
        if (nameEl) nameEl.focus();
        return;
    }

    // 3. Email Regex (Standard email format)
    let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        msg.innerHTML = "⚠️ <b>Notice:</b> Please enter a valid email address (e.g. name@example.com).";
        msg.className = "alert-box alert-danger";
        if (emailEl) emailEl.focus();
        return;
    }

    // 4. Phone Number Regex (Exactly 10 digits)
    let phoneRegex = /^[0-9]{10}$/;
    if (!phoneRegex.test(phone)) {
        msg.innerHTML = "⚠️ <b>Notice:</b> Phone number must be exactly 10 digits (0-9).";
        msg.className = "alert-box alert-danger";
        if (phoneEl) phoneEl.focus();
        return;
    }

    // 5. Username Regex (3 to 15 alphanumeric characters and underscores)
    let userRegex = /^[A-Za-z0-9_]{3,15}$/;
    if (!userRegex.test(username)) {
        msg.innerHTML = "⚠️ <b>Notice:</b> Username must be 3-15 characters (letters, numbers, or underscore).";
        msg.className = "alert-box alert-danger";
        if (usernameEl) usernameEl.focus();
        return;
    }

    // 6. Password Regex (Minimum 6 characters, at least 1 letter and 1 number)
    let passRegex = /^(?=.*[A-Za-z])(?=.*\d).{6,}$/;
    if (!passRegex.test(password)) {
        msg.innerHTML = "⚠️ <b>Notice:</b> Password must be at least 6 characters and contain at least one letter and one number.";
        msg.className = "alert-box alert-danger";
        if (passwordEl) passwordEl.focus();
        return;
    }

    // 7. Confirm Password Match
    if (password !== confirm) {
        msg.innerHTML = "⚠️ <b>Notice:</b> Passwords do not match. Please re-enter.";
        msg.className = "alert-box alert-danger";
        if (confirmEl) confirmEl.focus();
        return;
    }

    // 8. Gender Selection Check
    if (!genderEl) {
        msg.innerHTML = "⚠️ <b>Notice:</b> Please select your gender (Male/Female).";
        msg.className = "alert-box alert-danger";
        return;
    }

    // 9. Course Selection Check
    if (!course || course === "Select Course" || course === "") {
        msg.innerHTML = "⚠️ <b>Notice:</b> Please choose a course from the dropdown.";
        msg.className = "alert-box alert-danger";
        if (courseEl) courseEl.focus();
        return;
    }

    // 10. Check if account already exists
    let users = JSON.parse(localStorage.getItem("registeredUsers") || "[]");
    let existingUser = users.find(u => u.username.toLowerCase() === username.toLowerCase() || u.email.toLowerCase() === email.toLowerCase());
    if (existingUser) {
        msg.innerHTML = "⚠️ <b>Notice:</b> An account with this username or email already exists. Please login.";
        msg.className = "alert-box alert-danger";
        return;
    }

    // Save user to localStorage
    users.push({
        name: name,
        email: email,
        phone: phone,
        username: username,
        password: password,
        gender: genderEl.value,
        course: course
    });
    localStorage.setItem("registeredUsers", JSON.stringify(users));

    // Success Notice & Redirection
    msg.innerHTML = "✅ <b>Notice:</b> Registration Successful! Redirecting to Login page...";
    msg.className = "alert-box alert-success";
    setTimeout(function() {
        window.location.href = "login.html";
    }, 1200);
}

// --- 13. Result Page Functions ---
function calculate() {
    let marks = document.getElementsByClassName("marks");
    let total = 0;

    for (let i = 0; i < marks.length; i++) {
        total = total + Number(marks[i].innerHTML);
    }

    let percentage = total / 4;

    let totalEl = document.getElementById("total");
    if (totalEl) {
        totalEl.innerHTML = "Total Marks: " + total + " / 400";
    }

    let percEl = document.getElementById("percentage");
    if (percEl) {
        percEl.innerHTML = "Percentage: " + percentage + "%";
    }
}

function showResult() {
    let result = document.getElementById("result");
    if (result) {
        result.style.display = (result.style.display === "none") ? "block" : "none";
    }
}

// --- 14. Timetable Page Functions ---
function showTable() {
    let table = document.getElementById("timetable");
    if (table) {
        table.style.display = (table.style.display === "none") ? "block" : "none";
    }
}

// --- 15. Generic Form Reset Helper ---
function clearForm() {
    let form = document.querySelector("form");
    if (form) form.reset();

    let msg = document.getElementById("message");
    if (msg) {
        msg.style.display = "none";
        msg.innerHTML = "";
        msg.className = "";
    }

    let fileName = document.getElementById("fileName");
    if (fileName) fileName.innerHTML = "";

    let count = document.getElementById("count");
    if (count) {
        if (document.getElementById("msg")) {
            count.innerHTML = "Characters: 0 / 100";
        } else {
            count.innerHTML = "Characters: 0 / 50";
        }
    }
}
