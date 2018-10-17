
function drawDataTable(selector, headers, data) {
	var table = d3.select(selector)
		.append("table")
		.attr("class", "standard-table");
	var thead = table.append("thead");
	var tbody = table.append("tbody");
	thead.append("tr")
		.selectAll("th")
		.data(headers)
		.enter()
		.append("th")
		.text(function(header) { return header; });
	var rows = tbody.selectAll("tr")
		.data(data)
		.enter()
		.append("tr")
		.attr("class", "standard-table");
	var cells = rows.selectAll("td")
		.data(function(row) { return row; })
		.enter()
		.append("td")
		.attr("class", "standard-table")
		.text(function(row) { return row; });
}

function drawPieChart(selector, title, data, colorMap) {
	c3.generate({
		bindto: selector,
		data: {
			columns: data,
			type: "donut",
			colors: colorMap
		},
		donut: {
			title: title
		},
		legend: {
			show: false
		}
	});
}
