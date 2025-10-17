import React, { Component } from 'react';

class LineFillEffect extends Component {
    constructor(props) {
        super(props);
        this.lineRef = React.createRef();
        this.handleScroll = this.handleScroll.bind(this);
    }
    
    componentDidMount() {
        if (this.props.timeline_fill_setting) {
          requestAnimationFrame(()=>{
            this.handleScroll();
          })    
          window.addEventListener('scroll', this.handleScroll);
        }
    }

    componentDidUpdate(prevProps){
      requestAnimationFrame(()=>{
        this.handleScroll();
      })    
      window.addEventListener('scroll', this.handleScroll);
    }

    componentWillUnmount() {
        window.removeEventListener('scroll', this.handleScroll);
    }
    
    handleScroll() {
        if(this.lineRef.current === null || this.lineRef.current === undefined){
          return;
        }
        
        const tm = this.lineRef.current.closest('.tmdivi-wrapper');

        if(this.props.timeline_fill_setting === undefined || this.props.timeline_fill_setting === 'off'){
            tm.classList.remove('tmdivi-end-out-viewport')
            tm.classList.remove('tmdivi-start-fill')
            tm.classList.remove('tmdivi-end-fill')

            this.lineRef.current.style.height = '0px'
            return;
        }

        if (!tm) return;
    
        // grab lines and entries
        const outerLine = tm.querySelector('.tmdivi-line');
        const innerLine = tm.querySelector('.tmdivi-inner-line');
        const entries   = tm.querySelectorAll('.tmdivi-story');
        const years     = tm.querySelectorAll('.tmdivi-year-container');
    
        if (!outerLine || !innerLine) return;
    
        const halfViewport = window.innerHeight / 2;
        const rect         = outerLine.getBoundingClientRect();
        const timelineTop  = rect.top < 0
          ? Math.abs(rect.top)
          : -Math.abs(rect.top);
    
        const lineInnerHeight = timelineTop + halfViewport;
        const outerH          = outerLine.offsetHeight;
        // const scrollY         = document.documentElement.scrollTop;
        const timelineRect    = tm.getBoundingClientRect();
        const timelineTopOff  = timelineRect.top; // already relative to viewport
    
        tm.classList.add('tmdivi-start-out-viewport');
    
        if (lineInnerHeight <= outerH) {
          tm.classList.add('tmdivi-end-out-viewport', 'tmdivi-start-out-viewport');
          innerLine.style.height = `${lineInnerHeight}px`;
    
          if (timelineTopOff < halfViewport) {
            tm.classList.remove('tmdivi-start-out-viewport');
            tm.classList.add('tmdivi-start-fill');
          }
        } else {
          tm.classList.remove('tmdivi-end-out-viewport');
          tm.classList.add('tmdivi-end-fill');
          innerLine.style.height = `${outerH}px`;
        }
    
        // show/hide each story entry
        entries.forEach(entry => {
          const icon    = entry.querySelector('.tmdivi-icon, .tmdivi-icondot');
          const iconOff = icon ? icon.offsetTop : 0;
          const top     = entry.getBoundingClientRect().top + iconOff;
          
          entry.classList.toggle('tmdivi-out-viewport', top >= halfViewport);
        });
    
        // show/hide each year container
        years.forEach(yc => {
          const top = yc.getBoundingClientRect().top + 35;
          yc.classList.toggle('tmdivi-out-viewport', top >= halfViewport);
        });
    }
    
    render() {
        return (
            <div
                ref={this.lineRef}
                className="tmdivi-inner-line"
                style={this.props.timeline_fill_setting !== 'on' ? { height: '0px' } : {}}
            />
        );
    }
}

export { LineFillEffect };