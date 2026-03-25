@extends('layouts.student')

@section('title', 'Fee Structure')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Fee Structure</h2>
    <p class="text-muted mb-0">
        View your assigned fees and installment details
    </p>
</div>

<div id="feeMainContainer">

    <!-- Dynamic content will load here -->

    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <h3 id="feeTitle" class="text-primary fw-bold mb-1">--</h3>
                    <div class="text-muted">
                        <span id="feeClass"></span> •
                        <span id="feeAcademicYear"></span>
                    </div>
                    <div class="mt-2">
                        <span id="feeStatusBadge"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Academic Details</h5>
                </div>
                <div class="card-body" id="studentInfo"></div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Fee Breakdown</h5>
                </div>
                <div class="card-body" id="feeBreakdown"></div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Installment Summary</h5>
                </div>
                <div class="card-body" id="installmentSummary"></div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Installment Schedule</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="installmentTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

<div class="modal fade" id="payFeeModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Pay Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" id="payAmount" class="form-control" min="1">
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Mode</label>
                    <select id="paymentMode" class="form-control">
                        
                        <option value="UPI">UPI</option>
                        <option value="CARD">Card</option>
                        <option value="BANK_TRANSFER">Bank Transfer</option>
                        <option value="CHEQUE">Cheque</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Paid By</label>
                    <select id="paidBy" class="form-control">
                        <option value="STUDENT">Student</option>
                        <option value="PARENT">Parent</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea id="remarks" class="form-control"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-success" onclick="submitFeePayment()">
                    Pay Now
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')

<script>
document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    // const modalEl = document.getElementById("payFeeModal");
    // const payModal = new bootstrap.Modal(modalEl);

    if (!token || !user || user.role !== "student") {
        localStorage.clear();
        window.location.href = "{{ url('/login') }}";
        return;
    }

    let currentPaymentType = null;
    let currentInstallmentId = null;
    let currentAssignmentId = null;

    /* ===============================
       HEADER PROFILE
    =============================== */
    apiRequest("GET", "/me")
        .done(res => {
            document.getElementById("headerUserName").innerText = res.name;
            document.getElementById("headerUserRole").innerText = "Student";
        });

    document.getElementById("logoutBtn")
        .addEventListener("click", function () {
            localStorage.clear();
            window.location.href = "../page-login.html";
        });

    /* ===============================
       GET ASSIGNMENT ID
    =============================== */
    const params = new URLSearchParams(window.location.search);
    const assignmentId = params.get("assignment_id");

    if (!assignmentId) {
        loadAllGroupedFees();
        return;
    }

    currentAssignmentId = assignmentId;

    /* ===============================
       LOAD ALL GROUPED FEES
    =============================== */
    function loadAllGroupedFees() {

        apiRequest("GET", "/student/fees")
        .done(function(res) {

            const fees = Array.isArray(res.fees) ? res.fees : [];

            let html = "";

            if (fees.length === 0) {

                html = `
                    <div class="alert alert-info">
                        No fees assigned.
                    </div>
                `;
            }
            else {

                fees.forEach((fee, index) => {

                    let statusClass = "danger";

                    if (fee.status === "PAID")
                        statusClass = "success";
                    else if (fee.status === "PARTIAL")
                        statusClass = "warning";

                    html += `
                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card shadow-sm h-100 border-${statusClass}">
                                <div class="card-body d-flex flex-column">

                                    <h5 class="fw-bold mb-2">
                                        ${fee.title || fee.fee_structure?.name || "Fee"}
                                    </h5>

                                    <div class="mb-2 text-muted small">
                                        ${fee.class ?? "-"} • ${fee.academic_year ?? "-"}
                                    </div>

                                    <div class="mb-2">
                                        <strong>Total:</strong> 
                                        ₹ ${Number(fee.total_amount).toLocaleString()}
                                    </div>

                                    <div class="mb-3">
                                        <strong>Due:</strong> 
                                        ₹ ${Number(fee.due_amount).toLocaleString()}
                                    </div>

                                    <div class="mt-auto">

                                        <span class="badge bg-${statusClass} mb-2">
                                            ${fee.status}
                                        </span>

                                        <br>

                                        <a href="?assignment_id=${fee.assignment_id}"
                                        class="btn btn-primary btn-sm w-100">
                                            View Details
                                        </a>

                                    </div>

                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            document.getElementById("feeMainContainer").innerHTML = `
                <div class="row">
                    <div class="col-12 mb-3">
                        <h4 class="fw-bold">All Fees</h4>
                    </div>
                    ${html}
                </div>
            `;
        });
    }

    /* ===============================
       LOAD FEE DETAILS
    =============================== */

    apiRequest("GET", `/student/fees/${assignmentId}`)
        .done(function (res) {

            if (!res.assignment_id) {
                alert("Invalid fee data");
                return;
            }
            currentAssignmentId = res.assignment_id;
            
            /* ===============================
            TOP HEADER FEE INFO CARD
            =============================== */

            document.getElementById("feeTitle").innerText =
                res.title ?? "Fee Details";

            document.getElementById("feeClass").innerText =
                res.class ?? "-";

            document.getElementById("feeAcademicYear").innerText =
                res.academic_year ?? "-";


            let badgeClass = "bg-danger";

            if (res.status === "PAID")
                badgeClass = "bg-success";
            else if (res.status === "PARTIAL")
                badgeClass = "bg-warning";


            document.getElementById("feeStatusBadge").innerHTML = `
                <span class="badge ${badgeClass}">
                    ${res.status}
                </span>
            `;

            const items = res.items || [];
            const installments = res.installments || [];

            /* ===============================
               Academic Details
            =============================== */

            document.getElementById("studentInfo").innerHTML = `

                <h4 class="text-primary mb-3">${res.title}</h4>
                <p><strong>Class:</strong> ${res.class ?? '-'}</p>
                <p><strong>Section:</strong> ${res.section ?? '-'}</p>

                <p><strong>Academic Year:</strong> ${res.academic_year ?? '-'}</p>

                <p>
                    <strong>Status:</strong>
                    <span class="badge bg-${
                        res.status === 'PAID' ? 'success' :
                        res.status === 'PARTIAL' ? 'warning' :
                        'danger'
                    }">
                        ${res.status}
                    </span>
                </p>
            `;

            /* ===============================
               Fee Breakdown
            =============================== */

            let rows = "";

            if (items.length === 0) {
                rows = `
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            No fee items available
                        </td>
                    </tr>
                `;
            }
            else {
                items.forEach(item => {
                    rows += `
                        <tr>
                            <td>${item.fee_head}</td>
                            <td class="text-end">
                                ₹ ${parseFloat(item.amount).toLocaleString()}
                            </td>
                        </tr>
                    `;
                });
            }

            document.getElementById("feeBreakdown").innerHTML = `
                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Fee Head</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>

                    <tbody>

                        ${rows}

                        <tr class="fw-bold">
                            <td>Total</td>

                            <td class="text-end">
                                ₹ ${parseFloat(res.total_amount).toLocaleString()}
                            </td>
                        </tr>

                    </tbody>

                </table>
            `;

            /* ===============================
               FULL PAYMENT CASE
            =============================== */

            if (!installments.length) {

                const totalAmount = parseFloat(res.total_amount);

                document.getElementById("installmentSummary").innerHTML = `
                    <div class="text-center">

                        <h5>Full Payment Required</h5>

                        <p>Total Amount: ₹ ${totalAmount.toLocaleString()}</p>

                        ${
                            res.status !== "PAID"
                            ? `<button 
                                    class="btn btn-primary"
                                    onclick="payWithRazorpay(${res.assignment_id}, null)">
                                    Pay Now
                                </button>`
                            : `<span class="badge bg-success">Already Paid</span>`
                        }

                    </div>
                `;

                document.getElementById("installmentTable").innerHTML = `
                    <tr>
                        <td colspan="5">No installment plan available</td>
                    </tr>
                `;

                return;
            }

            /* ===============================
               INSTALLMENT CASE
            =============================== */

            let tableRows = "";
            let nextPendingFound = false;

            installments.forEach(inst => {

                let statusBadge = "";
                let actionBtn   = "";

                if (inst.status === "PAID") {

                    statusBadge = `<span class="badge bg-success">PAID</span>`;
                    actionBtn   = "✓";

                }
                else if (!nextPendingFound && inst.status === "PENDING") {

                    statusBadge = `<span class="badge bg-warning">PENDING</span>`;

                    actionBtn = `
                            <button 
                                class="btn btn-primary btn-sm"
                                onclick="payWithRazorpay(${res.assignment_id}, ${inst.id})">
                                    Pay Now
                            </button>
                        `;

                    nextPendingFound = true;
                }
                else {

                    statusBadge = `<span class="badge bg-secondary">UPCOMING</span>`;
                    actionBtn   = "Locked";
                }

                tableRows += `
                    <tr>

                        <td>${inst.installment_no}</td>

                        <td>${inst.due_date ?? '-'}</td>

                        <td>₹ ${parseFloat(inst.amount).toLocaleString()}</td>

                        <td>${statusBadge}</td>

                        <td>${actionBtn}</td>

                    </tr>
                `;
            });

            document.getElementById("installmentTable").innerHTML = tableRows;

        });

    /* ===============================
       PAYMENT HANDLERS
    =============================== */

    document.addEventListener("click", function (e) {

        if (e.target.classList.contains("pay-btn")) {

            currentPaymentType = "INSTALLMENT";

            currentInstallmentId = e.target.dataset.id;

            document.getElementById("payAmount").value = e.target.dataset.amount;

            // new bootstrap.Modal(document.getElementById("payFeeModal")).show();
            const modal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById("payFeeModal")
        );
        modal.show();
        }

        if (e.target.classList.contains("full-pay-btn")) {

            currentPaymentType = "FULL";

            document.getElementById("payAmount").value = e.target.dataset.amount;

            // new bootstrap.Modal(document.getElementById("payFeeModal")).show();
            const modal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById("payFeeModal")
        );
        modal.show();
        }

    });

    /* ===============================
       SUBMIT PAYMENT
    =============================== */

    window.submitFeePayment = function () {

        const amount = document.getElementById("payAmount").value;
        const payment_mode = document.getElementById("paymentMode").value;
        const paid_by = document.getElementById("paidBy").value;
        const remarks = document.getElementById("remarks").value;

        apiRequest("POST", `/student/fees/${currentAssignmentId}/pay`, {
            amount,
            payment_mode,
            paid_by,
            remarks
        })
       .done(function () {

            const modalEl = document.getElementById("payFeeModal");
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

            modal.hide();

            // After hiding, force clean safety
            setTimeout(() => {

                document.body.classList.remove("modal-open");
                document.body.style.overflow = "";
                document.body.style.paddingRight = "";

                document.querySelectorAll(".modal-backdrop")
                    .forEach(el => el.remove());

                window.location.reload();

            }, 300);

        })
        .fail(function (err) {
            alert(err.responseJSON?.message ?? "Payment failed");
        });
    };
    window.payWithRazorpay = function (assignmentId, installmentId = null) {

        apiRequest("POST", "/student/razorpay/create-order", {
            assignment_id: assignmentId,
            installment_id: installmentId
        })
        .done(function(res) {

            var options = {
                key: res.razorpay_key,
                amount: res.amount * 100,
                currency: "INR",
                name: "Rede School",
                description: "Fee Payment",
                order_id: res.order_id,

                handler: function (response) {

                    apiRequest("POST", "/student/razorpay/verify-payment", {
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    })
                    .done(function() {

                        showToast("Payment successful ", "success");

                        setTimeout(() => {
                            window.location.reload();
                        }, 800);

                    })
                    .fail(function(err) {
                        showToast(
                            err.responseJSON?.message ?? "Verification failed",
                            "danger"
                        );
                    });
                },

                theme: {
                    color: "#0d6efd"
                }
            };

            var rzp = new Razorpay(options);
            rzp.open();

        })
        .fail(function(err) {
            showToast(
                err.responseJSON?.message ?? "Order creation failed",
                "danger"
            );
        });
    };

    function showToast(message, type = "success") {

        const container = document.getElementById("globalToastContainer");
        if (!container) return alert(message); // fallback safety

        const toastId = "toast-" + Date.now();

        const bgClass =
            type === "success" ? "bg-success" :
            type === "danger" ? "bg-danger" :
            "bg-primary";

        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML("beforeend", toastHTML);

        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });

        toast.show();

        toastEl.addEventListener("hidden.bs.toast", function () {
            toastEl.remove();
        });
    }
});
</script>

@endpush

