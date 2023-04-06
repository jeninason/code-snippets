
public class SouthernCassowary extends Bird implements Endangered {

	public static final String CASSOWARY_DESCRIPTION = "Southern Cassowary";
	private boolean isEndangered;
	public static final boolean CASSOWARY_CAN_FLY = false;
	
	public SouthernCassowary (int id, String name) {
		super(id, name);
		this.isEndangered = true;
	}
	
	@Override
	public boolean canFly() {
		return CASSOWARY_CAN_FLY;
	}
	@Override
	public String getDescription() {
		return  super.getDescription() + CASSOWARY_DESCRIPTION + " ("			
				+ (canFly() ? "flies" : "does not fly")
				+ ((isEndangered) ? ", endangered)" : ")");
	}

	@Override
	public void displayConservationInformation() {
		System.out.println(getName()  + " says: \"Help save the dangerous " + CASSOWARY_DESCRIPTION + "!\"");
		
	}
	
}
