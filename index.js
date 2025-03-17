document.addEventListener("DOMContentLoaded", function () {
    fetch("fetch_properties.php") 
        .then(response => response.json())
        .then(responseData => {
            console.log("Fetched Data:", responseData); // Debugging

            // Ensure responseData contains the expected 'data' array
            if (!responseData.success || !Array.isArray(responseData.data)) {
                throw new Error("Invalid data format received.");
            }

            const properties = responseData.data; // Extract the array
            const propertyContainer = document.getElementById("property-container");
            propertyContainer.innerHTML = "";

            properties.forEach(property => {
                const propertyCard = document.createElement("div");
                propertyCard.className = "property-card";
                
                propertyCard.innerHTML = `
                    <div class="property-details">
                        <h3>${property.name}</h3>
                        <p><strong>Address:</strong> ${property.address}</p>
                        <p><strong>Type:</strong> ${property.type}</p>
                        <p><strong>Status:</strong> ${property.status}</p>
                        <p><strong>Rent:</strong> KES ${property.rent}/month</p>
                        <p><strong>Landlord:</strong> ${property.landlord}</p>
                        
                        ${property.tenants.length > 0 ? '<h4>Tenants:</h4>' : ''}
                        <ul>
                            ${property.tenants.map(tenant => `
                                <li>${tenant.name} (Lease: ${tenant.lease_start} to ${tenant.lease_end})</li>
                            `).join('')}
                        </ul>
                    </div>
                `;
                
                propertyContainer.appendChild(propertyCard);
            });
        })
        .catch(error => console.error("Error fetching properties:", error));
});
