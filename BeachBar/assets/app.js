const seats = document.querySelectorAll(".seat");

const modal =
    document.getElementById("reservationModal");

const seatTitle =
    document.getElementById("seatTitle");

const reservationId =
    document.getElementById("reservationId");

const beachSpotId =
    document.getElementById("beachSpotId");

const customerName =
    document.getElementById("customerName");

const phone =
    document.getElementById("phone");

const comment =
    document.getElementById("comment");

const status =
    document.getElementById("status");

const closeModal =
    document.getElementById("closeModal");

const deleteBtn =
    document.getElementById("deleteBtn");

const reservationForm =
    document.getElementById("reservationForm");

/*
TOAST SYSTEM
*/

function showToast(message, type = "success") {

    const toast =
        document.createElement("div");

    toast.innerText = message;

    toast.style.position = "fixed";

    toast.style.bottom = "20px";

    toast.style.right = "20px";

    toast.style.padding =
        "14px 18px";

    toast.style.borderRadius =
        "14px";

    toast.style.color = "white";

    toast.style.fontWeight = "600";

    toast.style.zIndex = "99999";

    toast.style.boxShadow =
        "0 10px 25px rgba(0,0,0,0.2)";

    toast.style.backdropFilter =
        "blur(10px)";

    toast.style.transition =
        "0.3s";

    toast.style.opacity = "0";

    toast.style.transform =
        "translateY(20px)";

    if(type === "success") {

        toast.style.background =
            "linear-gradient(135deg,#10b981,#059669)";

    } else {

        toast.style.background =
            "linear-gradient(135deg,#ef4444,#dc2626)";
    }

    document.body.appendChild(toast);

    setTimeout(() => {

        toast.style.opacity = "1";

        toast.style.transform =
            "translateY(0px)";

    }, 100);

    setTimeout(() => {

        toast.style.opacity = "0";

        toast.style.transform =
            "translateY(20px)";

        setTimeout(() => {

            toast.remove();

        }, 300);

    }, 3000);
}

/*
OPEN MODAL
*/

seats.forEach(seat => {

    seat.addEventListener("click", () => {

        const seatId = seat.dataset.id;

        const seatName = seat.dataset.name;

        let reservation = null;

        try {

            reservation =
                JSON.parse(seat.dataset.reservation);

        } catch(error) {

            reservation = null;
        }

        seatTitle.innerText =
            "Umbrella " + seatName;

        beachSpotId.value = seatId;

        /*
        EXISTING RESERVATION
        */

        if(reservation) {

            reservationId.value =
                reservation.id || "";

            customerName.value =
                reservation.customer_name || "";

            phone.value =
                reservation.phone || "";

            comment.value =
                reservation.comment || "";

            status.value =
                reservation.status || "reserved";

            deleteBtn.style.display =
                "inline-block";

        }

        /*
        NEW RESERVATION
        */

        else {

            reservationId.value = "";

            customerName.value = "";

            phone.value = "";

            comment.value = "";

            status.value = "reserved";

            deleteBtn.style.display =
                "none";
        }

        /*
        REQUIRED FIELD
        */

        toggleCustomerNameRequirement();

        modal.style.display = "block";

    });

});

/*
STATUS CHANGE
*/

status.addEventListener("change", () => {

    toggleCustomerNameRequirement();

});

/*
TOGGLE NAME REQUIRED
*/

function toggleCustomerNameRequirement() {

    if(status.value === "fake_reserved") {

        customerName.required = false;

        customerName.placeholder =
            "Optional for fake reservation";

    } else {

        customerName.required = true;

        customerName.placeholder =
            "Customer Name";
    }

}

/*
CLOSE MODAL
*/

closeModal.addEventListener("click", () => {

    modal.style.display = "none";

});

/*
CLICK OUTSIDE
*/

window.addEventListener("click", (e) => {

    if(e.target === modal) {

        modal.style.display = "none";
    }

});

/*
SAVE
*/

reservationForm.addEventListener(
    "submit",
    async (e) => {

    e.preventDefault();

    const formData =
        new FormData(reservationForm);

    try {

        const response = await fetch(
            "../api/save_reservation.php",
            {
                method: "POST",
                body: formData
            }
        );

        const result =
            await response.text();

        if(
            result.toLowerCase().includes("saved")
            ||
            result.toLowerCase().includes("updated")
        ) {

            showToast(result, "success");

            setTimeout(() => {

                location.reload();

            }, 700);

        } else {

            showToast(result, "error");
        }

    } catch(error) {

        showToast(
            "Error saving reservation",
            "error"
        );

        console.error(error);
    }

});

/*
DELETE
*/

deleteBtn.addEventListener(
    "click",
    async () => {

    const formData = new FormData();

    formData.append(
        "reservation_id",
        reservationId.value
    );

    try {

        const response = await fetch(
            "../api/delete_reservation.php",
            {
                method: "POST",
                body: formData
            }
        );

        const result =
            await response.text();

        showToast(result, "success");

        setTimeout(() => {

            location.reload();

        }, 700);

    } catch(error) {

        showToast(
            "Error deleting reservation",
            "error"
        );

        console.error(error);
    }

});