class H1{

  constructor(nbins, xlo, xup){
    this.nbins=nbins;
    this.xlo=xlo;
    this.xup=xup;
    this.xstep=(xup-xlo)/nbins;
    this.xdata=new Array(nbins+2);
    for(let bin=0; bin<nbins+2; bin++){
      this.xdata[bin]=0;
    }
  }
  
  fill(value, weight=1){
    value=parseFloat(value);
    weight=parseFloat(weight);
    let bin=0;
    if(value < this.xlo){bin=0;} 
    else if(value > this.xup){bin=this.nbins+1;}
    else{
      for(bin=0; bin<this.nbins; bin++){
        if((this.xlo+this.xstep*bin)<=value && value<(this.xlo+this.xstep*(bin+1))){
          break;
        }
      }
    }
    this.xdata[bin]+=weight;
    //console.log("Fill: value="+value+" bin="+bin+" weight="+weight+" data="+this.data[bin]);
  }
  
	get data(){
		return this.xdata.slice(1,-1);  
	}
	
	get bins(){
    var bb=new Array(this.nbins);
		for (let bin=0;bin<this.nbins;bin++){
			bb[bin]=(this.xlo+this.xstep*bin).toFixed(2);
		}
		return bb;
	}
}

class Profile{

  constructor(nbins, xlo, xup){
    this.nbins=nbins;
    this.xlo=xlo;
    this.xup=xup;
    this.xstep=(xup-xlo)/nbins;
    this.xdata=new Array(nbins+2);
    this.xsums=new Array(nbins+2);
    this.xsqrs=new Array(nbins+2);
    this.xnums=new Array(nbins+2);
    for(let bin=0; bin<this.nbins+2; bin++){
      this.xdata[bin]=0;
      this.xsums[bin]=0;
      this.xsqrs[bin]=0;
      this.xnums[bin]=0;
    }
  }
  
  fill(value, weight=1){
    value=parseFloat(value);
    weight=parseFloat(weight);
    let bin=0;
    if(value < this.xlo){bin=0;} 
    else if(value > this.xup){bin=this.nbins+1;}
    else{
      for(bin=0; bin<this.nbins; bin++){
        if((this.xlo+this.xstep*bin)<=value && value<(this.xlo+this.xstep*(bin+1))){
          break;
        }
      }
    }
    this.xsums[bin]+=weight;
    this.xsqrs[bin]+=weight*weight;
		this.xnums[bin]+=1.;
		this.xdata[bin]=this.xsums[bin]/this.xnums[bin];
    //console.log("Fill: value="+value+" bin="+bin+" weight="+weight+" data="+this.xdata[bin]);    
	}

	get data(){
		return this.xdata.slice(1,-1);  
	}
	
	get bins(){
    var bb=new Array(this.nbins);
		for (let bin=0;bin<this.nbins;bin++){
			bb[bin]=(this.xlo+this.xstep*bin).toFixed(2);
		}
		return bb;
	}

	get errs(){
    var bb=new Array(this.nbins);
		for (let bin=0;bin<this.nbins;bin++){
			bb[bin]=Math.sqrt(this.xsqrs[bin]/this.xnums[bin] - Math.pow(this.xsums[bin]/this.xnums[bin],2));
		}
		return bb;
	}
  
}

class Profile1{

  constructor(nbins, xlo, xup){
    this.nbins=nbins;
    this.xlo=xlo;
    this.xup=xup;
    this.xstep=(xup-xlo)/nbins;
    this.data=new Array(nbins+2);
    this.sumy=new Array(nbins+2);
    this.numy=new Array(nbins+2);
    this.erry=new Array(nbins+2);
    this.bins=new Array(nbins+2);
    let bin=0;
    for(bin=0; bin<this.nbins+2; bin++){
      this.bins[bin]=(this.xlo+this.xstep*bin).toFixed(2);
      this.data[bin]=0;
      this.sumy[bin]=0;
      this.numy[bin]=0;
      this.erry[bin]=0;
    }
  }
  
  fill(value, weight=1){
    value=parseFloat(value);
    weight=parseFloat(weight);
    let bin=0;
    if(value < this.xlo){bin=0;} 
    else if(value > this.xup){bin=this.nbins+1;}
    else{
      for(bin=1; bin<this.nbins+1; bin++){
        if(value>=(this.xlo+this.xstep*bin) && value<(this.xlo+this.xstep*(bin+1)) ){
          break;
        }
      }
    }
    this.sumy[bin]+=weight;
		this.numy[bin]+=1.;
		this.data[bin]=this.sumy[bin]/this.numy[bin];
    //console.log("Fill: value="+value+" bin="+bin+" weight="+weight+" data="+this.data[bin]);
    
	}
  
}
