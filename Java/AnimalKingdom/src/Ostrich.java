
public class Ostrich extends Bird {

	public static final String OSTRICH_DESCRIPTION = "Ostrich";
	public static final boolean OSTRICH_CAN_FLY = false;
	
	public Ostrich(int id, String name) {
		super(id, name); //all ostriches lay eggs, no need to allow setting of birthtype
	}
	
	@Override
	public boolean canFly() {
		return OSTRICH_CAN_FLY;
	}
	@Override
	public String getDescription() {
		return  super.getDescription() + OSTRICH_DESCRIPTION + " ("			
				+ (canFly() ? "flies" : "does not fly")
				+ ")";
	}


}