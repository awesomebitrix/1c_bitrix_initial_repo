class BarsChart {
  constructor(args) {
    this.node = $(args.node)
    this.data = args.data
    this.unit = args.unit

    const margin = {top: 16, right: 0, bottom: 0, left: 0}
    const width = this.node.width() - margin.left - margin.right
    const height = this.node.height() - margin.top - margin.bottom


    var color = d3.scale.linear()
      .domain([0, 1])
      .range(['#e5e8ff', '#6375ff'])

    var y = d3.scale.linear()
      .domain([0, d3.max(this.data, (d) => d)])
      .range([height, 0])

    var svg = d3.select(this.node[0])
      .append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
      .append('g')
        .attr('transform', "translate(" + margin.left + "," + margin.top + ")");

    svg.selectAll('rect')
      .data(this.data.reverse()).enter()
      .append('rect')
        .attr('x', (d, i) => (this.node.width() - 64) / 2 + i * -16)
        .attr('y', (d) => y(d))
        .attr('width', 80)
        .attr('height', (d) => height - y(d))
        .attr('fill', (d, i) => color(i))

    this.data.reverse()

    console.log();

    var t = svg
      .append(`text`)
        .attr(`y`,  (height - y(this.data[0]) - 36 > 0) ? y(this.data[0]) : height - 36)
        .attr('fill', '#6475ff')
        .style(`text-anchor`, `end`)

    t.append('tspan')
        .attr(`x`, this.node.width() / 2 - 48 - 16)
        .attr('dy', '.75em')
        .text('Бесплатно')

    t.append('tspan')
        .attr(`x`, this.node.width() / 2 - 48 - 16)
        .attr('dy', '1.25em')
        .style('font-weight', 500)
        .text(`${this.data[0]} ${this.unit}`)

    var t = svg
      .append(`text`)
        .attr(`y`, (height - y(this.data[1]) - 36 > 0) ? y(this.data[1]) : height - 36)
        .style(`text-anchor`, `start`)

    t.append('tspan')
        .attr(`x`, this.node.width() / 2 + 48 + 16)
        .attr('dy', '.75em')
        .text('Исходящие')

    t.append('tspan')
        .attr(`x`, this.node.width() / 2 + 48 + 16)
        .attr('dy', '1.25em')
        .style('font-weight', 500)
        .text(`${this.data[1]} ${this.unit}`)
  }

  update() {
  }
};

var bars = new BarsChart({
  node: '.chart-canvas',
  data: [100, 140],
  unit: 'SMS'
});

bars.update()
