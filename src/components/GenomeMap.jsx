import React, { Component } from 'react';
import { scaleLinear } from 'd3';
import generateLinesFromMap from '../utils/generateLinesFromMap';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { setSelectedChromosome } from '../redux/actions/actions';
import { LABEL_WIDTH, CHART_WIDTH, TRACK_HEIGHT } from '../utils/chartConstants';
import { drawLinesByColor, drawSubTracksByType, drawLabels } from '../utils/canvasUtilities';
import TreeMap from './TreeMap';
// A global scale that gets updated each time the chart is drawn again
let chromosomeScale;

class GenomeMap extends Component {

    chromosomeClick = (event) => {
        const chromosomeID = event.currentTarget.id.split('-')[1];
        this.props.setSelectedChromosome(chromosomeID);
    }

    componentDidMount() {
        const { lineMap = {}, cnvMap = {}, genomeMap = {} } = this.props,
            { validChromosomeList, chromosomeScale } = getChromosomeVectors(genomeMap);

        // create a list of line names from the lineMap of the first valid chromosome
        const lineNames = _.map(lineMap[validChromosomeList[0]], (d) => d.lineName);

        _.map(validChromosomeList, (chrom) => {
            const subLineMap = lineMap[chrom] || [],
                subGenomeMap = genomeMap[chrom],
                subCNVMap = cnvMap[chrom],
                subWidth = chromosomeScale(subGenomeMap.referenceMap.length);
            if (subLineMap.length > 0) {
                drawChart(this['canvas-' + chrom], subWidth, subLineMap, subGenomeMap, subCNVMap, lineNames);
            }
        });
        // Also draw labels for each line 
        drawLabels(this['canvas-label'], lineNames);
    }

    render() {
        const { genomeMap = {}, treeMap = {}, lineMap = {}, selectedChromosome = '' } = this.props,
            { validChromosomeList, chromosomeScale } = getChromosomeVectors(genomeMap);

        const canvasList = _.map(validChromosomeList, (chrom, chromIndex) => {
            const subWidth = chromosomeScale(genomeMap[chrom].referenceMap.length);
            return <div
                key={"canvas-" + chromIndex}
                id={'chromID-' + chrom}
                className={'genomemap-canvas-wrapper ' + (selectedChromosome == chrom ? 'selected' : '')}
                onClick={this.chromosomeClick}>
                <canvas className='genomemap-canvas'
                    width={subWidth}
                    height={(_.keys(lineMap[chrom]).length * TRACK_HEIGHT)}
                    ref={(el) => { this['canvas-' + chrom] = el }} />
                <h3>{chrom}</h3>
            </div>
        });

        // Add in the a separate canvas just for label names
        canvasList.push(<canvas key="canvas-label" className='genomemap-canvas-label'
            width={LABEL_WIDTH}
            height={(_.keys(lineMap[validChromosomeList[0]]).length * TRACK_HEIGHT)}
            ref={(el) => { this['canvas-label'] = el }} />);

        return (<div className='genomemap-container visible-lg-inline-block'>
            <h4 className='text-primary chart-title'>Genome</h4>
            <TreeMap treeMap={treeMap} treeID='genomeTree'/>
            {canvasList}
        </div>);
    }
}

function drawChart(canvas, subWidth, lineMap, genomeMap, cnvMap, lineNames) {
    const lineDataLength = genomeMap.referenceMap.length,
        xScale = scaleLinear()
            .domain([0, lineDataLength - 1])
            .range([0, subWidth]);
    drawLinesByColor(canvas, generateLinesFromMap(lineMap, xScale));
    // drawSubTracksByType(canvas, generateCNVMarkerPositions(cnvMap, lineNames, genomeMap, xScale));
}

function getChromosomeVectors(genomeMap) {
    // make sure list only has chromosomes and not unmapped IDs
    const validChromosomeList = _.keys(genomeMap),
        // 5 pixel gap between chromosomes
        availableWidth = CHART_WIDTH - ((validChromosomeList.length - 1) * 5),
        totalMarkerCount = _.reduce(validChromosomeList,
            ((acc, chr) => acc + genomeMap[chr].referenceMap.length), 0);
    // create a scale for the entire chromosome 
    chromosomeScale = scaleLinear().domain([0, totalMarkerCount]).range([0, availableWidth]);
    return { validChromosomeList, chromosomeScale };
}

function mapDispatchToProps(dispatch) {
    return {
        setSelectedChromosome: bindActionCreators(setSelectedChromosome, dispatch)
    };
}

function mapStateToProps(state) {
    return { selectedChromosome: state.oracle.selectedChromosome };
}

export default connect(mapStateToProps, mapDispatchToProps)(GenomeMap);



