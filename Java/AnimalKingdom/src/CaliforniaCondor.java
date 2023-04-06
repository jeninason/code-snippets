
public class CaliforniaCondor extends Bird implements Endangered {

	public static final String CONDOR_DESCRIPTION = "California Condor";
	private boolean isEndangered;
	
	public CaliforniaCondor(int id, String name) {
		super(id, name);
		this.isEndangered = true;
	}
	
	@Override
	public String getDescription() {
		return  super.getDescription() + CONDOR_DESCRIPTION + " ("			
				+ (canFly() ? "flies" : "does not fly")
				+ ((isEndangered) ? ", endangered)" : ")");
	}

	@Override
	public void displayConservationInformation() {
		System.out.print("Help save the breathtaking " + CONDOR_DESCRIPTION + "!");
		
	}


}