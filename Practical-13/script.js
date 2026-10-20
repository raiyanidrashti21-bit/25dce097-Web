
const apiURL = "api.php";

const form = document.getElementById("studentForm");
const studentList = document.getElementById("studentList");
const message = document.getElementById("message");
const loading = document.getElementById("loading");
const saveButton = document.getElementById("saveButton");

function showMessage(text, type = "success") {
    message.textContent = text;
    message.className = type;
}

async function requestAPI(method, data = null) {
    const options = { method };

    if (data !== null) {
        options.headers = {
            "Content-Type": "application/json"
        };
        options.body = JSON.stringify(data);
    }

    const response = await fetch(apiURL, options);
    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || "Request failed");
    }

    return result;
}

async function loadStudents() {
    loading.hidden = false;

    try {
        const result = await requestAPI("GET");
        studentList.replaceChildren();

        result.data.forEach(student => {
            const row = document.createElement("tr");

            [
                student.id,
                student.name,
                student.email,
                student.mobile,
                student.course
            ].forEach(value => {
                const cell = document.createElement("td");
                cell.textContent = value;
                row.appendChild(cell);
            });

            const actions = document.createElement("td");

            const editButton = document.createElement("button");
            editButton.textContent = "Edit";
            editButton.type = "button";

            editButton.addEventListener("click", () => {
                document.getElementById("id").value = student.id;
                document.getElementById("name").value = student.name;
                document.getElementById("email").value = student.email;
                document.getElementById("mobile").value = student.mobile;
                document.getElementById("course").value = student.course;

                saveButton.textContent = "Update Student";
            });

            const deleteButton = document.createElement("button");
            deleteButton.textContent = "Delete";
            deleteButton.type = "button";
            deleteButton.style.marginLeft = "5px";

            deleteButton.addEventListener("click", async () => {
                if (!confirm("Delete this student?")) return;

                try {
                    await requestAPI("POST", {
                        action: "delete",
                        id: student.id
                    });

                    showMessage("Student deleted successfully");
                    await loadStudents();
                } catch (error) {
                    showMessage(error.message, "error");
                }
            });

            actions.append(editButton, deleteButton);
            row.appendChild(actions);
            studentList.appendChild(row);
        });
    } catch (error) {
        showMessage(error.message, "error");
    } finally {
        loading.hidden = true;
    }
}

form.addEventListener("submit", async event => {
    event.preventDefault();

    const id = document.getElementById("id").value;

    const data = {
        action: id ? "update" : "add",
        name: document.getElementById("name").value.trim(),
        email: document.getElementById("email").value.trim(),
        mobile: document.getElementById("mobile").value.trim(),
        course: document.getElementById("course").value.trim()
    };

    if (id) data.id = id;

    if (!data.name || !data.email ||
        !data.mobile || !data.course) {
        showMessage("Please fill in all fields", "error");
        return;
    }

    if (!/^[0-9]{10}$/.test(data.mobile)) {
        showMessage("Enter a valid 10-digit mobile number", "error");
        return;
    }

    saveButton.disabled = true;

    try {
        const result = await requestAPI("POST", data);

        showMessage(result.message);
        form.reset();
        document.getElementById("id").value = "";
        saveButton.textContent = "Add Student";

        await loadStudents();
    } catch (error) {
        showMessage(error.message, "error");
    } finally {
        saveButton.disabled = false;
    }
});

document.getElementById("cancelButton").addEventListener("click", () => {
    form.reset();
    document.getElementById("id").value = "";
    saveButton.textContent = "Add Student";
    showMessage("Edit cancelled");
});

loadStudents();
